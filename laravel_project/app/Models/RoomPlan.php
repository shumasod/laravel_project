<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class RoomPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'name',
        'code',
        'description',
        'meal_type',
        'meal_description',
        'base_price',
        'child_price',
        'infant_price',
        'date_prices',
        'sale_start_date',
        'sale_end_date',
        'stay_start_date',
        'stay_end_date',
        'min_nights',
        'max_nights',
        'min_guests',
        'max_guests',
        'available_days',
        'cancellation_policy_id',
        'point_rate',
        'benefits',
        'is_active',
        'is_featured',
        'badge_text',
        'display_order',
    ];

    protected $casts = [
        'date_prices' => 'array',
        'available_days' => 'array',
        'benefits' => 'array',
        'sale_start_date' => 'date',
        'sale_end_date' => 'date',
        'stay_start_date' => 'date',
        'stay_end_date' => 'date',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'point_rate' => 'decimal:2',
    ];

    const MEAL_ROOM_ONLY = 'room_only';
    const MEAL_BREAKFAST = 'breakfast_only';
    const MEAL_DINNER = 'dinner_only';
    const MEAL_HALF_BOARD = 'half_board';
    const MEAL_FULL_BOARD = 'full_board';
    const MEAL_ALL_INCLUSIVE = 'all_inclusive';

    /**
     * 部屋
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * キャンセルポリシー
     */
    public function cancellationPolicy(): BelongsTo
    {
        return $this->belongsTo(CancellationPolicy::class);
    }

    /**
     * 在庫
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(PlanInventory::class, 'room_plan_id');
    }

    /**
     * オプション
     */
    public function options(): BelongsToMany
    {
        return $this->belongsToMany(PlanOption::class, 'room_plan_options')
            ->withPivot('override_price');
    }

    /**
     * 予約
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * 有効なプランのみ
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 販売中のプラン
     */
    public function scopeOnSale($query)
    {
        $today = Carbon::today();
        return $query->where(function ($q) use ($today) {
            $q->whereNull('sale_start_date')->orWhere('sale_start_date', '<=', $today);
        })->where(function ($q) use ($today) {
            $q->whereNull('sale_end_date')->orWhere('sale_end_date', '>=', $today);
        });
    }

    /**
     * 特定日付の料金を取得
     */
    public function getPriceForDate(Carbon $date): int
    {
        $dateKey = $date->format('Y-m-d');

        // 日付別料金があればそれを使用
        if ($this->date_prices && isset($this->date_prices[$dateKey])) {
            return (int) $this->date_prices[$dateKey];
        }

        // 在庫の特別価格をチェック
        $inventory = $this->inventories()
            ->where('date', $date)
            ->first();

        if ($inventory && $inventory->price) {
            return $inventory->price;
        }

        return $this->base_price;
    }

    /**
     * 期間の合計料金を計算
     */
    public function calculateTotalPrice(Carbon $checkIn, Carbon $checkOut, int $guests = 1): array
    {
        $nights = $checkIn->diffInDays($checkOut);
        $totalPrice = 0;
        $breakdown = [];

        $current = $checkIn->copy();
        for ($i = 0; $i < $nights; $i++) {
            $price = $this->getPriceForDate($current) * $guests;
            $breakdown[] = [
                'date' => $current->format('Y-m-d'),
                'price' => $price,
            ];
            $totalPrice += $price;
            $current->addDay();
        }

        return [
            'total' => $totalPrice,
            'nights' => $nights,
            'guests' => $guests,
            'per_night_average' => $nights > 0 ? round($totalPrice / $nights) : 0,
            'breakdown' => $breakdown,
        ];
    }

    /**
     * 在庫チェック
     */
    public function checkAvailability(Carbon $checkIn, Carbon $checkOut, int $rooms = 1): bool
    {
        $current = $checkIn->copy();
        $nights = $checkIn->diffInDays($checkOut);

        for ($i = 0; $i < $nights; $i++) {
            $inventory = $this->inventories()
                ->where('date', $current)
                ->first();

            if (!$inventory || $inventory->is_closed || $inventory->available_inventory < $rooms) {
                return false;
            }

            $current->addDay();
        }

        return true;
    }

    /**
     * 食事タイプの表示名
     */
    public function getMealTypeNameAttribute(): string
    {
        return match($this->meal_type) {
            self::MEAL_ROOM_ONLY => '素泊まり',
            self::MEAL_BREAKFAST => '朝食付き',
            self::MEAL_DINNER => '夕食付き',
            self::MEAL_HALF_BOARD => '1泊2食付き',
            self::MEAL_FULL_BOARD => '3食付き',
            self::MEAL_ALL_INCLUSIVE => 'オールインクルーシブ',
            default => $this->meal_type,
        };
    }

    /**
     * 無料キャンセル可能かチェック
     */
    public function hasFreeCancellation(): bool
    {
        if (!$this->cancellationPolicy) {
            return false;
        }

        $rules = $this->cancellationPolicy->rules ?? [];
        foreach ($rules as $rule) {
            if (($rule['charge_percent'] ?? 100) === 0) {
                return true;
            }
        }

        return false;
    }
}
