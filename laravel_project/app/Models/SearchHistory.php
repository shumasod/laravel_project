<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'session_id',
        'search_params',
        'result_count',
    ];

    protected $casts = [
        'search_params' => 'array',
    ];

    /**
     * 顧客
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * 検索履歴を記録
     */
    public static function record(array $params, int $resultCount, ?int $customerId = null, ?string $sessionId = null): self
    {
        return self::create([
            'customer_id' => $customerId,
            'session_id' => $sessionId,
            'search_params' => $params,
            'result_count' => $resultCount,
        ]);
    }

    /**
     * 最近の検索を取得
     */
    public static function getRecent(?int $customerId = null, ?string $sessionId = null, int $limit = 10)
    {
        return self::when($customerId, fn($q) => $q->where('customer_id', $customerId))
            ->when($sessionId && !$customerId, fn($q) => $q->where('session_id', $sessionId))
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * 検索条件の概要を取得
     */
    public function getSummaryAttribute(): string
    {
        $params = $this->search_params;
        $parts = [];

        if (!empty($params['destination'])) {
            $parts[] = $params['destination'];
        }

        if (!empty($params['check_in']) && !empty($params['check_out'])) {
            $parts[] = "{$params['check_in']}〜{$params['check_out']}";
        }

        if (!empty($params['guests'])) {
            $parts[] = "{$params['guests']}名";
        }

        return implode(' / ', $parts) ?: '検索条件なし';
    }
}
