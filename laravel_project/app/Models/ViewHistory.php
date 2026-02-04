<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViewHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'session_id',
        'accommodation_id',
        'view_count',
        'last_viewed_at',
    ];

    protected $casts = [
        'last_viewed_at' => 'datetime',
    ];

    /**
     * 顧客
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * 宿泊施設
     */
    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }

    /**
     * 閲覧履歴を記録
     */
    public static function record(int $accommodationId, ?int $customerId = null, ?string $sessionId = null): self
    {
        $history = self::where('accommodation_id', $accommodationId)
            ->when($customerId, fn($q) => $q->where('customer_id', $customerId))
            ->when($sessionId && !$customerId, fn($q) => $q->where('session_id', $sessionId))
            ->first();

        if ($history) {
            $history->increment('view_count');
            $history->update(['last_viewed_at' => now()]);
            return $history;
        }

        return self::create([
            'customer_id' => $customerId,
            'session_id' => $sessionId,
            'accommodation_id' => $accommodationId,
            'view_count' => 1,
            'last_viewed_at' => now(),
        ]);
    }

    /**
     * 最近閲覧した施設を取得
     */
    public static function getRecent(?int $customerId = null, ?string $sessionId = null, int $limit = 10)
    {
        return self::with('accommodation')
            ->when($customerId, fn($q) => $q->where('customer_id', $customerId))
            ->when($sessionId && !$customerId, fn($q) => $q->where('session_id', $sessionId))
            ->orderBy('last_viewed_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
