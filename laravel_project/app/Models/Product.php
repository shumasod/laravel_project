<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 商品マスター
 *
 * WHY: 在庫数は products.stock_quantity に非正規化して持つが、
 * 必ず stock_transactions から復元可能にする（Single Source of Truth）
 */
class Product extends Model
{
    protected $fillable = [
        'sku',
        'name',
        'description',
        'reorder_point',
        'warehouse_id',
    ];

    protected $guarded = [
        'stock_quantity',
    ];

    protected $casts = [
        'stock_quantity' => 'integer',
        'reorder_point' => 'integer',
    ];

    /**
     * 在庫取引履歴
     */
    public function stockTransactions(): HasMany
    {
        return $this->hasMany(StockTransaction::class);
    }

    /**
     * 在庫切れかどうか
     */
    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }

    /**
     * 発注点を下回っているか（アラート表示用）
     */
    public function isBelowReorderPoint(): bool
    {
        return $this->stock_quantity <= $this->reorder_point;
    }

    /**
     * スコープ: 在庫が少ない順
     */
    public function scopeLowStockFirst($query)
    {
        return $query->orderBy('stock_quantity', 'asc');
    }

    /**
     * スコープ: 発注点を下回っている商品のみ
     */
    public function scopeBelowReorderPoint($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'reorder_point');
    }
}
