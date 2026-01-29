<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Landmark extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_kana',
        'category',
        'area_id',
        'description',
        'latitude',
        'longitude',
        'image_url',
        'is_popular',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_popular' => 'boolean',
    ];

    const CATEGORY_ONSEN = 'onsen';           // 温泉地
    const CATEGORY_THEME_PARK = 'theme_park'; // テーマパーク
    const CATEGORY_NATURE = 'nature';         // 自然
    const CATEGORY_HISTORY = 'history';       // 歴史・文化
    const CATEGORY_BEACH = 'beach';           // ビーチ・海
    const CATEGORY_MOUNTAIN = 'mountain';     // 山・高原
    const CATEGORY_CITY = 'city';             // 都市・繁華街
    const CATEGORY_SHRINE = 'shrine';         // 神社・寺院

    /**
     * エリア
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * カテゴリで絞り込み
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * 人気スポット
     */
    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    /**
     * 名前で検索
     */
    public function scopeSearch($query, string $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%")
              ->orWhere('name_kana', 'like', "%{$keyword}%");
        });
    }

    /**
     * 周辺の宿泊施設を取得
     */
    public function getNearbyAccommodations(int $radiusKm = 10, int $limit = 20)
    {
        if (!$this->latitude || !$this->longitude) {
            return collect();
        }

        return Accommodation::selectRaw("
            *,
            (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance
        ", [$this->latitude, $this->longitude, $this->latitude])
            ->having('distance', '<', $radiusKm)
            ->orderBy('distance')
            ->limit($limit)
            ->get();
    }

    /**
     * カテゴリ表示名
     */
    public function getCategoryNameAttribute(): string
    {
        return match($this->category) {
            self::CATEGORY_ONSEN => '温泉地',
            self::CATEGORY_THEME_PARK => 'テーマパーク',
            self::CATEGORY_NATURE => '自然',
            self::CATEGORY_HISTORY => '歴史・文化',
            self::CATEGORY_BEACH => 'ビーチ・海',
            self::CATEGORY_MOUNTAIN => '山・高原',
            self::CATEGORY_CITY => '都市・繁華街',
            self::CATEGORY_SHRINE => '神社・寺院',
            default => 'その他',
        };
    }
}
