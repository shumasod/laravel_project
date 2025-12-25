<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReservationService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected PricingService $pricingService
    ) {}

    /**
     * 新規予約を作成
     */
    public function createReservation(array $data): Reservation
    {
        return DB::transaction(function () use ($data) {
            $room = Room::findOrFail($data['room_id']);
            $customer = Customer::findOrFail($data['customer_id']);

            $checkIn = Carbon::parse($data['check_in_date']);
            $checkOut = Carbon::parse($data['check_out_date']);
            $numberOfGuests = $data['number_of_guests'] ?? 1;

            // 在庫チェックと予約
            $available = $this->inventoryService->checkAvailability(
                $room->accommodation_id,
                $room->room_type,
                $checkIn,
                $checkOut
            );

            if (!$available) {
                throw new \Exception('指定された日程で部屋が空いていません。');
            }

            // 料金計算
            $pricing = $this->pricingService->calculateTotalPrice(
                $room,
                $checkIn,
                $checkOut,
                $numberOfGuests
            );

            // 予約作成
            $reservation = Reservation::create([
                'customer_id' => $customer->id,
                'room_id' => $room->id,
                'number_of_guests' => $numberOfGuests,
                'check_in_date' => $checkIn,
                'check_out_date' => $checkOut,
                'status' => Reservation::STATUS_PROVISIONAL,
                'total_amount' => $pricing['total_amount'],
                'applied_discounts' => $pricing['applied_discounts'],
                'price_breakdown' => $pricing['breakdown'],
                'notes' => $data['notes'] ?? null,
                'created_by_user_id' => $data['user_id'] ?? null,
            ]);

            // 在庫を予約
            $reserved = $this->inventoryService->reserveInventory(
                $room->accommodation_id,
                $room->room_type,
                $checkIn,
                $checkOut
            );

            if (!$reserved) {
                throw new \Exception('在庫の予約に失敗しました。');
            }

            // ステータス履歴を記録
            $reservation->statusHistories()->create([
                'from_status' => null,
                'to_status' => Reservation::STATUS_PROVISIONAL,
                'notes' => '予約作成',
                'changed_by_user_id' => $data['user_id'] ?? null,
            ]);

            return $reservation->fresh(['room', 'customer', 'statusHistories']);
        });
    }

    /**
     * 予約を確定
     */
    public function confirmReservation(Reservation $reservation, ?int $userId = null): bool
    {
        return DB::transaction(function () use ($reservation, $userId) {
            return $reservation->changeStatus(
                Reservation::STATUS_CONFIRMED,
                $userId,
                '予約確定'
            );
        });
    }

    /**
     * 予約をキャンセル
     */
    public function cancelReservation(
        Reservation $reservation,
        ?string $reason = null,
        ?int $userId = null
    ): bool {
        return DB::transaction(function () use ($reservation, $reason, $userId) {
            $success = $reservation->changeStatus(
                Reservation::STATUS_CANCELLED,
                $userId,
                $reason ?? 'キャンセル'
            );

            if ($success) {
                // 在庫を解放
                $this->inventoryService->releaseInventory(
                    $reservation->room->accommodation_id,
                    $reservation->room->room_type,
                    $reservation->check_in_date,
                    $reservation->check_out_date
                );

                // キャンセル理由を記録
                if ($reason) {
                    $reservation->cancellation_reason = $reason;
                    $reservation->save();
                }
            }

            return $success;
        });
    }

    /**
     * ノーショーとして記録
     */
    public function markAsNoShow(Reservation $reservation, ?int $userId = null): bool
    {
        return DB::transaction(function () use ($reservation, $userId) {
            $success = $reservation->changeStatus(
                Reservation::STATUS_NO_SHOW,
                $userId,
                'ノーショー'
            );

            if ($success) {
                // 在庫を解放
                $this->inventoryService->releaseInventory(
                    $reservation->room->accommodation_id,
                    $reservation->room->room_type,
                    $reservation->check_in_date,
                    $reservation->check_out_date
                );
            }

            return $success;
        });
    }

    /**
     * 予約を変更
     */
    public function updateReservation(Reservation $reservation, array $data): Reservation
    {
        return DB::transaction(function () use ($reservation, $data) {
            $hasDateChange = isset($data['check_in_date']) || isset($data['check_out_date']);

            if ($hasDateChange) {
                $newCheckIn = isset($data['check_in_date'])
                    ? Carbon::parse($data['check_in_date'])
                    : $reservation->check_in_date;

                $newCheckOut = isset($data['check_out_date'])
                    ? Carbon::parse($data['check_out_date'])
                    : $reservation->check_out_date;

                // 在庫調整
                $adjusted = $this->inventoryService->adjustInventoryForReservationChange(
                    $reservation,
                    $newCheckIn,
                    $newCheckOut
                );

                if (!$adjusted) {
                    throw new \Exception('新しい日程で部屋が空いていません。');
                }

                // 料金再計算
                $pricing = $this->pricingService->calculateTotalPrice(
                    $reservation->room,
                    $newCheckIn,
                    $newCheckOut,
                    $data['number_of_guests'] ?? $reservation->number_of_guests
                );

                $data['total_amount'] = $pricing['total_amount'];
                $data['applied_discounts'] = $pricing['applied_discounts'];
                $data['price_breakdown'] = $pricing['breakdown'];
            }

            $reservation->update($data);

            if (isset($data['user_id'])) {
                $reservation->updated_by_user_id = $data['user_id'];
                $reservation->save();
            }

            return $reservation->fresh(['room', 'customer', 'statusHistories']);
        });
    }

    /**
     * 権限チェック
     */
    public function canUserModifyReservation(Reservation $reservation, int $userId, string $role): bool
    {
        // 管理者は全ての予約を変更可能
        if (in_array($role, ['admin', 'manager'])) {
            return true;
        }

        // スタッフは確定済み以降の予約のみ操作可能
        if ($role === 'staff') {
            return in_array($reservation->status, [
                Reservation::STATUS_CONFIRMED,
                Reservation::STATUS_CHECKED_IN,
            ]);
        }

        // 顧客は自分の仮予約のみキャンセル可能
        if ($role === 'customer') {
            return $reservation->created_by_user_id === $userId
                && $reservation->status === Reservation::STATUS_PROVISIONAL;
        }

        return false;
    }

    /**
     * 予約可能な日程を検索
     */
    public function searchAvailableRooms(
        int $accommodationId,
        Carbon $checkIn,
        Carbon $checkOut,
        ?string $roomType = null,
        int $numberOfGuests = 1
    ): array {
        $query = Room::where('accommodation_id', $accommodationId)
            ->where('is_available', true);

        if ($roomType) {
            $query->where('room_type', $roomType);
        }

        $query->where('capacity', '>=', $numberOfGuests);

        $rooms = $query->get();

        $availableRooms = [];

        foreach ($rooms as $room) {
            $isAvailable = $this->inventoryService->checkAvailability(
                $accommodationId,
                $room->room_type,
                $checkIn,
                $checkOut
            );

            if ($isAvailable) {
                $pricing = $this->pricingService->calculateTotalPrice(
                    $room,
                    $checkIn,
                    $checkOut,
                    $numberOfGuests
                );

                $availableRooms[] = [
                    'room' => $room,
                    'pricing' => $pricing,
                ];
            }
        }

        return $availableRooms;
    }
}
