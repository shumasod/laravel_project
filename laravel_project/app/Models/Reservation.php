<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reservation extends Model
{
    use HasFactory;

    // ステータス定数
    public const STATUS_PROVISIONAL = 'provisional';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CHECKED_IN = 'checked_in';
    public const STATUS_CHECKED_OUT = 'checked_out';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_NO_SHOW = 'no_show';

    // ステータス遷移ルール
    public const STATUS_TRANSITIONS = [
        self::STATUS_PROVISIONAL => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED],
        self::STATUS_CONFIRMED => [self::STATUS_CHECKED_IN, self::STATUS_CANCELLED, self::STATUS_NO_SHOW],
        self::STATUS_CHECKED_IN => [self::STATUS_CHECKED_OUT],
        self::STATUS_CHECKED_OUT => [],
        self::STATUS_CANCELLED => [],
        self::STATUS_NO_SHOW => [],
    ];

    protected $fillable = [
        'customer_id',
        'room_id',
        'number_of_guests',
        'check_in_date',
        'check_out_date',
        'status',
        'payment_status',
        'actual_check_in_time',
        'actual_check_out_time',
        'total_amount',
        'applied_discounts',
        'price_breakdown',
        'cancelled_at',
        'cancellation_reason',
        'notes',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'actual_check_in_time' => 'datetime',
        'actual_check_out_time' => 'datetime',
        'total_amount' => 'decimal:2',
        'applied_discounts' => 'array',
        'price_breakdown' => 'array',
        'cancelled_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ReservationStatusHistory::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function review(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Review::class);
    }

    /**
     * ステータス遷移が可能かチェック
     */
    public function canTransitionTo(string $newStatus): bool
    {
        $allowedStatuses = self::STATUS_TRANSITIONS[$this->status] ?? [];
        return in_array($newStatus, $allowedStatuses);
    }

    /**
     * ステータスを変更
     */
    public function changeStatus(string $newStatus, ?int $userId = null, ?string $notes = null): bool
    {
        if (!$this->canTransitionTo($newStatus)) {
            return false;
        }

        $oldStatus = $this->status;
        $this->status = $newStatus;

        if ($newStatus === self::STATUS_CANCELLED) {
            $this->cancelled_at = now();
        }

        $this->save();

        // 履歴を記録
        $this->statusHistories()->create([
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'notes' => $notes,
            'changed_by_user_id' => $userId,
        ]);

        return true;
    }

    /**
     * チェックイン処理
     */
    public function checkIn(?int $userId = null): bool
    {
        if ($this->changeStatus(self::STATUS_CHECKED_IN, $userId, 'Checked in')) {
            $this->actual_check_in_time = now();
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * チェックアウト処理
     */
    public function checkOut(?int $userId = null): bool
    {
        if ($this->changeStatus(self::STATUS_CHECKED_OUT, $userId, 'Checked out')) {
            $this->actual_check_out_time = now();
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * 宿泊日数を計算
     */
    public function getNumberOfNights(): int
    {
        return $this->check_in_date->diffInDays($this->check_out_date);
    }
}
