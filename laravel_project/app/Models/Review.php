<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'customer_id',
        'accommodation_id',
        'overall_rating',
        'cleanliness_rating',
        'service_rating',
        'location_rating',
        'value_rating',
        'amenities_rating',
        'title',
        'comment',
        'photos',
        'is_verified',
        'is_published',
        'admin_response',
        'admin_responded_at',
        'helpful_count',
    ];

    protected $casts = [
        'overall_rating' => 'integer',
        'cleanliness_rating' => 'integer',
        'service_rating' => 'integer',
        'location_rating' => 'integer',
        'value_rating' => 'integer',
        'amenities_rating' => 'integer',
        'photos' => 'array',
        'is_verified' => 'boolean',
        'is_published' => 'boolean',
        'admin_responded_at' => 'datetime',
    ];

    /**
     * リレーション: 予約
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * リレーション: 顧客
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * リレーション: 宿泊施設
     */
    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }

    /**
     * リレーション: 役立ち投票をした顧客
     */
    public function helpfulVoters(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'review_helpful_votes')
            ->withTimestamps();
    }

    /**
     * 平均評価を計算
     */
    public function getAverageDetailRating(): float
    {
        $ratings = array_filter([
            $this->cleanliness_rating,
            $this->service_rating,
            $this->location_rating,
            $this->value_rating,
            $this->amenities_rating,
        ]);

        if (empty($ratings)) {
            return $this->overall_rating;
        }

        return round(array_sum($ratings) / count($ratings), 1);
    }

    /**
     * レビューを公開
     */
    public function publish(): void
    {
        $this->update(['is_published' => true]);
    }

    /**
     * レビューを非公開
     */
    public function unpublish(): void
    {
        $this->update(['is_published' => false]);
    }

    /**
     * レビューを認証済みにマーク
     */
    public function verify(): void
    {
        $this->update(['is_verified' => true]);
    }

    /**
     * 管理者が返信
     */
    public function addAdminResponse(string $response): void
    {
        $this->update([
            'admin_response' => $response,
            'admin_responded_at' => now(),
        ]);
    }

    /**
     * 役立ち投票を追加
     */
    public function addHelpfulVote(Customer $customer): void
    {
        if (!$this->helpfulVoters()->where('customer_id', $customer->id)->exists()) {
            $this->helpfulVoters()->attach($customer->id);
            $this->increment('helpful_count');
        }
    }

    /**
     * 役立ち投票を削除
     */
    public function removeHelpfulVote(Customer $customer): void
    {
        if ($this->helpfulVoters()->where('customer_id', $customer->id)->exists()) {
            $this->helpfulVoters()->detach($customer->id);
            $this->decrement('helpful_count');
        }
    }

    /**
     * スコープ: 公開済み
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * スコープ: 認証済み
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * スコープ: 評価でフィルタ
     */
    public function scopeWithRating($query, int $rating)
    {
        return $query->where('overall_rating', $rating);
    }
}
