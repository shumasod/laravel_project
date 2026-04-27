<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Accommodation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'description',
        'phone',
        'email',
        // Travel search extensions
        'facility_type',
        'description_long',
        'check_in_time',
        'check_out_time',
        'check_in_end',
        'latitude',
        'longitude',
        'area_id',
        'nearest_station_id',
        'station_distance_minutes',
        'star_rating',
        'review_score',
        'review_count',
        'cleanliness_score',
        'service_score',
        'location_score',
        'facility_score',
        'value_score',
        'highlight_features',
        'parking_info',
        'access_info',
        'min_price',
        'max_price',
        'is_featured',
        'is_new',
        'display_priority',
        'cancellation_policy_id',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'review_score' => 'decimal:1',
        'cleanliness_score' => 'decimal:1',
        'service_score' => 'decimal:1',
        'location_score' => 'decimal:1',
        'facility_score' => 'decimal:1',
        'value_score' => 'decimal:1',
        'highlight_features' => 'array',
        'parking_info' => 'array',
        'access_info' => 'array',
        'is_featured' => 'boolean',
        'is_new' => 'boolean',
    ];

    /**
     * エリア
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * 最寄り駅
     */
    public function nearestStation(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'nearest_station_id');
    }

    /**
     * キャンセルポリシー
     */
    public function cancellationPolicy(): BelongsTo
    {
        return $this->belongsTo(CancellationPolicy::class);
    }

    /**
     * 写真
     */
    public function photos(): HasMany
    {
        return $this->hasMany(AccommodationPhoto::class)->orderBy('display_order');
    }

    /**
     * アメニティ
     */
    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'accommodation_amenities')
            ->withPivot('note')
            ->withTimestamps();
    }

    /**
     * 宿泊プラン（部屋経由）
     */
    public function roomPlans()
    {
        return RoomPlan::whereHas('room', fn($q) => $q->where('accommodation_id', $this->id));
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * 公開済みレビューの平均評価を取得
     */
    public function getAverageRating(): float
    {
        return $this->reviews()
            ->published()
            ->avg('overall_rating') ?? 0;
    }

    /**
     * 公開済みレビュー数を取得
     */
    public function getReviewCount(): int
    {
        return $this->reviews()
            ->published()
            ->count();
    }
}
