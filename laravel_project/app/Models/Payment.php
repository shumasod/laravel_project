<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'amount',
        'payment_method',
        'payment_gateway',
        'payment_details',
        'notes',
    ];

    protected $guarded = [
        'status',
        'transaction_id',
        'paid_at',
        'refunded_at',
        'refund_amount',
        'refund_reason',
        'failure_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
        'payment_details' => 'array',
    ];

    /**
     * リレーション: 予約
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * 決済完了処理
     */
    public function markAsPaid(string $transactionId = null): void
    {
        $this->status = 'completed';
        $this->paid_at = now();
        $this->transaction_id = $transactionId ?? $this->transaction_id;
        $this->save();
    }

    /**
     * 決済失敗処理
     */
    public function markAsFailed(string $reason): void
    {
        $this->status = 'failed';
        $this->failure_reason = $reason;
        $this->save();
    }

    /**
     * 返金処理
     */
    public function refund(float $amount = null, string $reason = null): void
    {
        $this->status = 'refunded';
        $this->refunded_at = now();
        $this->refund_amount = $amount ?? $this->amount;
        $this->refund_reason = $reason;
        $this->save();
    }

    /**
     * 決済がキャンセル可能か
     */
    public function isCancellable(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    /**
     * 決済が返金可能か
     */
    public function isRefundable(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * 部分返金可能か
     */
    public function isPartiallyRefundable(): bool
    {
        return $this->status === 'completed' &&
               ($this->refund_amount === null || $this->refund_amount < $this->amount);
    }
}
