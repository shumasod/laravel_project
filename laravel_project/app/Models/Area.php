<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_kana',
        'name_en',
        'parent_id',
        'level',
        'code',
        'latitude',
        'longitude',
        'accommodation_count',
        'display_order',
        'is_popular',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_popular' => 'boolean',
    ];

    const LEVEL_REGION = 'region';
    const LEVEL_PREFECTURE = 'prefecture';
    const LEVEL_CITY = 'city';
    const LEVEL_DISTRICT = 'district';

    /**
     * 親エリア
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'parent_id');
    }

    /**
     * 子エリア
     */
    public function children(): HasMany
    {
        return $this->hasMany(Area::class, 'parent_id')->orderBy('display_order');
    }

    /**
     * 宿泊施設
     */
    public function accommodations(): HasMany
    {
        return $this->hasMany(Accommodation::class);
    }

    /**
     * 駅
     */
    public function stations(): HasMany
    {
        return $this->hasMany(Station::class);
    }

    /**
     * 観光スポット
     */
    public function landmarks(): HasMany
    {
        return $this->hasMany(Landmark::class);
    }

    /**
     * 都道府県のみ取得
     */
    public function scopePrefectures($query)
    {
        return $query->where('level', self::LEVEL_PREFECTURE);
    }

    /**
     * 人気エリア
     */
    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    /**
     * 階層パスを取得（例: 関東 > 東京都 > 渋谷区）
     */
    public function getPathAttribute(): string
    {
        $path = [$this->name];
        $current = $this;

        while ($current->parent) {
            $current = $current->parent;
            array_unshift($path, $current->name);
        }

        return implode(' > ', $path);
    }

    /**
     * 都道府県を取得
     */
    public function getPrefectureAttribute(): ?Area
    {
        if ($this->level === self::LEVEL_PREFECTURE) {
            return $this;
        }

        $current = $this;
        while ($current->parent) {
            if ($current->parent->level === self::LEVEL_PREFECTURE) {
                return $current->parent;
            }
            $current = $current->parent;
        }

        return null;
    }
}
