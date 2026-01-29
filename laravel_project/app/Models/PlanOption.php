<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PlanOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'price_type',
        'is_required',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    const PRICE_PER_PERSON = 'per_person';
    const PRICE_PER_ROOM = 'per_room';
    const PRICE_PER_STAY = 'per_stay';

    /**
     * このオプションを持つプラン
     */
    public function roomPlans(): BelongsToMany
    {
        return $this->belongsToMany(RoomPlan::class, 'room_plan_options')
            ->withPivot('override_price');
    }

    /**
     * 料金タイプの表示名
     */
    public function getPriceTypeNameAttribute(): string
    {
        return match($this->price_type) {
            self::PRICE_PER_PERSON => '1人あたり',
            self::PRICE_PER_ROOM => '1室あたり',
            self::PRICE_PER_STAY => '1滞在あたり',
            default => $this->price_type,
        };
    }
}
