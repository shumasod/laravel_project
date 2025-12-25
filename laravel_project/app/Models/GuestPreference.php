<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'allergies',
        'dietary_restrictions',
        'special_requests',
        'smoking_preference',
        'bed_preference',
        'floor_preference',
        'quiet_room_preference',
        'preferred_contact_method',
        'preferred_language',
        'notes',
    ];

    protected $casts = [
        'smoking_preference' => 'boolean',
        'quiet_room_preference' => 'boolean',
    ];

    /**
     * 個人情報フィールドの暗号化
     */
    protected $encrypted = [
        'allergies',
        'dietary_restrictions',
        'special_requests',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * アレルギー情報を取得（暗号化対応）
     */
    public function getAllergiesAttribute($value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return decrypt($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    /**
     * アレルギー情報を設定（暗号化）
     */
    public function setAllergiesAttribute($value): void
    {
        if ($value) {
            $this->attributes['allergies'] = encrypt($value);
        } else {
            $this->attributes['allergies'] = null;
        }
    }

    /**
     * 食事制限情報を取得（暗号化対応）
     */
    public function getDietaryRestrictionsAttribute($value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return decrypt($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    /**
     * 食事制限情報を設定（暗号化）
     */
    public function setDietaryRestrictionsAttribute($value): void
    {
        if ($value) {
            $this->attributes['dietary_restrictions'] = encrypt($value);
        } else {
            $this->attributes['dietary_restrictions'] = null;
        }
    }

    /**
     * 特別なリクエストを取得（暗号化対応）
     */
    public function getSpecialRequestsAttribute($value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return decrypt($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    /**
     * 特別なリクエストを設定（暗号化）
     */
    public function setSpecialRequestsAttribute($value): void
    {
        if ($value) {
            $this->attributes['special_requests'] = encrypt($value);
        } else {
            $this->attributes['special_requests'] = null;
        }
    }
}
