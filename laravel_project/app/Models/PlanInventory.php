<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanInventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_plan_id',
        'date',
        'total_inventory',
        'available_inventory',
        'price',
        'is_closed',
    ];

    protected $casts = [
        'date' => 'date',
        'is_closed' => 'boolean',
    ];

    /**
     * プラン
     */
    public function roomPlan(): BelongsTo
    {
        return $this->belongsTo(RoomPlan::class);
    }

    /**
     * 利用可能な在庫のみ
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_closed', false)
            ->where('available_inventory', '>', 0);
    }

    /**
     * 日付範囲で取得
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * 残室が少ないかどうか
     */
    public function isLowStock(int $threshold = 3): bool
    {
        return $this->available_inventory > 0 && $this->available_inventory <= $threshold;
    }
}
