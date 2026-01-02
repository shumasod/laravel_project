<?php

namespace App\Models;

use App\Enums\StockTransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 在庫取引履歴
 *
 * WHY: すべての在庫変動をここに記録することで監査証跡を残す
 * この履歴から stock_quantity を復元できることが重要
 */
class StockTransaction extends Model
{
    // 履歴は更新しない（updated_at不要）
    const UPDATED_AT = null;

    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'type' => StockTransactionType::class,
        'quantity' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * 対象商品
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * スコープ: 特定の種別のみ
     */
    public function scopeOfType($query, StockTransactionType $type)
    {
        return $query->where('type', $type->value);
    }

    /**
     * スコープ: 日付範囲でフィルタ
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
