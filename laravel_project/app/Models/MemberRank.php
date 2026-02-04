<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MemberRank extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'min_spending',
        'point_rate',
        'color',
        'benefits',
        'display_order',
    ];

    protected $casts = [
        'point_rate' => 'decimal:2',
        'benefits' => 'array',
    ];

    /**
     * このランクの顧客
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * 利用金額からランクを決定
     */
    public static function determineRank(int $spending): ?self
    {
        return self::where('min_spending', '<=', $spending)
            ->orderBy('min_spending', 'desc')
            ->first();
    }

    /**
     * ポイント計算
     */
    public function calculatePoints(int $amount): int
    {
        return (int) floor($amount * $this->point_rate);
    }
}
