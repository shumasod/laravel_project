<?php

namespace App\Services;

use App\Models\RoomInventory;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class InventoryService
{
    /**
     * 在庫を初期化
     */
    public function initializeInventory(
        int $accommodationId,
        string $roomType,
        Carbon $startDate,
        Carbon $endDate,
        int $totalRooms
    ): void {
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            RoomInventory::updateOrCreate(
                [
                    'accommodation_id' => $accommodationId,
                    'room_type' => $roomType,
                    'date' => $date->format('Y-m-d'),
                ],
                [
                    'total_rooms' => $totalRooms,
                    'available_rooms' => $totalRooms,
                    'reserved_rooms' => 0,
                ]
            );
        }
    }

    /**
     * 在庫の空き状況をチェック（排他制御付き）
     */
    public function checkAvailability(
        int $accommodationId,
        string $roomType,
        Carbon $checkIn,
        Carbon $checkOut,
        int $quantity = 1
    ): bool {
        return DB::transaction(function () use ($accommodationId, $roomType, $checkIn, $checkOut, $quantity) {
            $period = CarbonPeriod::create($checkIn, $checkOut->subDay());

            foreach ($period as $date) {
                $inventory = RoomInventory::where('accommodation_id', $accommodationId)
                    ->where('room_type', $roomType)
                    ->where('date', $date->format('Y-m-d'))
                    ->lockForUpdate() // 排他ロック
                    ->first();

                if (!$inventory || $inventory->available_rooms < $quantity) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * 在庫を予約（排他制御付き）
     */
    public function reserveInventory(
        int $accommodationId,
        string $roomType,
        Carbon $checkIn,
        Carbon $checkOut,
        int $quantity = 1
    ): bool {
        return DB::transaction(function () use ($accommodationId, $roomType, $checkIn, $checkOut, $quantity) {
            $period = CarbonPeriod::create($checkIn, $checkOut->subDay());

            // 全ての日付で在庫があるか確認
            foreach ($period as $date) {
                $inventory = RoomInventory::where('accommodation_id', $accommodationId)
                    ->where('room_type', $roomType)
                    ->where('date', $date->format('Y-m-d'))
                    ->lockForUpdate()
                    ->first();

                if (!$inventory || $inventory->available_rooms < $quantity) {
                    // ロールバック
                    return false;
                }
            }

            // 全て確認できたら、在庫を減らす
            foreach ($period as $date) {
                $inventory = RoomInventory::where('accommodation_id', $accommodationId)
                    ->where('room_type', $roomType)
                    ->where('date', $date->format('Y-m-d'))
                    ->lockForUpdate()
                    ->first();

                $inventory->reserve($quantity);
            }

            return true;
        });
    }

    /**
     * 在庫を解放（キャンセル時）
     */
    public function releaseInventory(
        int $accommodationId,
        string $roomType,
        Carbon $checkIn,
        Carbon $checkOut,
        int $quantity = 1
    ): void {
        DB::transaction(function () use ($accommodationId, $roomType, $checkIn, $checkOut, $quantity) {
            $period = CarbonPeriod::create($checkIn, $checkOut->subDay());

            foreach ($period as $date) {
                $inventory = RoomInventory::where('accommodation_id', $accommodationId)
                    ->where('room_type', $roomType)
                    ->where('date', $date->format('Y-m-d'))
                    ->lockForUpdate()
                    ->first();

                if ($inventory) {
                    $inventory->release($quantity);
                }
            }
        });
    }

    /**
     * 予約変更時の在庫調整
     */
    public function adjustInventoryForReservationChange(
        Reservation $reservation,
        Carbon $newCheckIn,
        Carbon $newCheckOut
    ): bool {
        return DB::transaction(function () use ($reservation, $newCheckIn, $newCheckOut) {
            $roomType = $reservation->room->room_type;
            $accommodationId = $reservation->room->accommodation_id;

            // 元の予約の在庫を解放
            $this->releaseInventory(
                $accommodationId,
                $roomType,
                $reservation->check_in_date,
                $reservation->check_out_date
            );

            // 新しい日付で在庫を予約
            return $this->reserveInventory(
                $accommodationId,
                $roomType,
                $newCheckIn,
                $newCheckOut
            );
        });
    }

    /**
     * 在庫状況を取得
     */
    public function getInventoryStatus(
        int $accommodationId,
        string $roomType,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        return RoomInventory::where('accommodation_id', $accommodationId)
            ->where('room_type', $roomType)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('date')
            ->get()
            ->map(function ($inventory) {
                return [
                    'date' => $inventory->date->format('Y-m-d'),
                    'total' => $inventory->total_rooms,
                    'available' => $inventory->available_rooms,
                    'reserved' => $inventory->reserved_rooms,
                    'occupancy_rate' => $inventory->total_rooms > 0
                        ? round(($inventory->reserved_rooms / $inventory->total_rooms) * 100, 2)
                        : 0,
                ];
            })
            ->toArray();
    }
}
