<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccommodationPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'accommodation_id',
        'url',
        'thumbnail_url',
        'caption',
        'category',
        'is_main',
        'display_order',
    ];

    protected $casts = [
        'is_main' => 'boolean',
    ];

    const CATEGORY_EXTERIOR = 'exterior';
    const CATEGORY_ROOM = 'room';
    const CATEGORY_BATH = 'bath';
    const CATEGORY_MEAL = 'meal';
    const CATEGORY_FACILITY = 'facility';
    const CATEGORY_OTHER = 'other';

    /**
     * 宿泊施設
     */
    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }

    /**
     * メイン写真のみ
     */
    public function scopeMain($query)
    {
        return $query->where('is_main', true);
    }

    /**
     * カテゴリで絞り込み
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * カテゴリ表示名
     */
    public function getCategoryNameAttribute(): string
    {
        return match($this->category) {
            self::CATEGORY_EXTERIOR => '外観',
            self::CATEGORY_ROOM => '客室',
            self::CATEGORY_BATH => '風呂',
            self::CATEGORY_MEAL => '食事',
            self::CATEGORY_FACILITY => '施設',
            default => 'その他',
        };
    }
}
