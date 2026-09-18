<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class PointTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'description',
        'reservation_id',
        'expire_date',
    ];

    protected $guarded = [
        'type',
        'points',
        'balance_after',
    ];

    protected $casts = [
        'expire_date' => 'date',
    ];

    const TYPE_EARN = 'earn';
    const TYPE_USE = 'use';
    const TYPE_EXPIRE = 'expire';
    const TYPE_ADJUST = 'adjust';
    const TYPE_BONUS = 'bonus';

    /**
     * 顧客
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * 予約
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * ポイント付与
     */
    public static function earn(
        int $customerId,
        int $points,
        string $description,
        ?int $reservationId = null,
        ?string $expireDate = null
    ): self {
        return DB::transaction(function () use ($customerId, $points, $description, $reservationId, $expireDate) {
            $customer = Customer::lockForUpdate()->findOrFail($customerId);
            $newBalance = $customer->total_points + $points;
            $customer->total_points = $newBalance;
            $customer->save();

            $tx = new self();
            $tx->customer_id = $customerId;
            $tx->type = self::TYPE_EARN;
            $tx->points = $points;
            $tx->balance_after = $newBalance;
            $tx->description = $description;
            $tx->reservation_id = $reservationId;
            $tx->expire_date = $expireDate;
            $tx->save();

            return $tx;
        });
    }

    /**
     * ポイント使用
     */
    public static function use(
        int $customerId,
        int $points,
        string $description,
        ?int $reservationId = null
    ): self {
        return DB::transaction(function () use ($customerId, $points, $description, $reservationId) {
            $customer = Customer::lockForUpdate()->findOrFail($customerId);

            if ($customer->total_points < $points) {
                throw new \Exception('ポイントが不足しています');
            }

            $newBalance = $customer->total_points - $points;
            $customer->total_points = $newBalance;
            $customer->save();

            $tx = new self();
            $tx->customer_id = $customerId;
            $tx->type = self::TYPE_USE;
            $tx->points = -$points;
            $tx->balance_after = $newBalance;
            $tx->description = $description;
            $tx->reservation_id = $reservationId;
            $tx->save();

            return $tx;
        });
    }

    /**
     * 期限切れ処理
     */
    public static function expirePoints(): int
    {
        $today = now()->toDateString();
        $expiredCount = 0;

        $expiringTransactions = self::where('type', self::TYPE_EARN)
            ->where('expire_date', '<', $today)
            ->where('points', '>', 0)
            ->get()
            ->groupBy('customer_id');

        foreach ($expiringTransactions as $customerId => $transactions) {
            $totalExpiring = $transactions->sum('points');

            // 実際にはより複雑なFIFOロジックが必要
            // ここでは簡略化

            $expiredCount += $totalExpiring;
        }

        return $expiredCount;
    }
}
