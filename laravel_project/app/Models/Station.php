<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Station extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_kana',
        'line_name',
        'line_code',
        'area_id',
        'latitude',
        'longitude',
        'is_major',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_major' => 'boolean',
    ];

    /**
     * エリア
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * 最寄りの宿泊施設
     */
    public function nearbyAccommodations(): HasMany
    {
        return $this->hasMany(Accommodation::class, 'nearest_station_id');
    }

    /**
     * 主要駅のみ
     */
    public function scopeMajor($query)
    {
        return $query->where('is_major', true);
    }

    /**
     * 路線名で検索
     */
    public function scopeOnLine($query, string $lineName)
    {
        return $query->where('line_name', 'like', "%{$lineName}%");
    }

    /**
     * 名前で検索（サジェスト用）
     */
    public function scopeSearch($query, string $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%")
              ->orWhere('name_kana', 'like', "%{$keyword}%");
        });
    }

    /**
     * 表示名（路線名付き）
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->line_name) {
            return "{$this->name}（{$this->line_name}）";
        }
        return $this->name;
    }
}
