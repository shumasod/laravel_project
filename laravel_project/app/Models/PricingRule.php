<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PricingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'accommodation_id',
        'room_type',
        'rule_type',
        'name',
        'description',
        'conditions',
        'calculation_type',
        'value',
        'priority',
        'is_active',
        'valid_from',
        'valid_to',
    ];

    protected $casts = [
        'conditions' => 'array',
        'value' => 'decimal:2',
        'is_active' => 'boolean',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }

    /**
     * ルールが有効かチェック
     */
    public function isValid(Carbon $date): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->valid_from && $date->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_to && $date->gt($this->valid_to)) {
            return false;
        }

        return true;
    }

    /**
     * ルールを適用して価格を計算
     */
    public function applyRule(float $basePrice): float
    {
        return match($this->calculation_type) {
            'fixed' => $this->value,
            'percentage' => $basePrice * (1 + $this->value / 100),
            'multiplier' => $basePrice * $this->value,
            default => $basePrice,
        };
    }
}
