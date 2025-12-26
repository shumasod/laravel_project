<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Accommodation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'description',
        'phone',
        'email',
    ];

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
