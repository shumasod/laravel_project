<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PoliticalParty extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_name',
        'english_name',
        'color',
        'founded_date',
        'dissolved_date',
        'description',
        'is_active',
    ];

    protected $casts = [
        'founded_date' => 'date',
        'dissolved_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * 選挙結果との関連
     */
    public function electionResults(): HasMany
    {
        return $this->hasMany(ElectionResult::class, 'party_id');
    }

    /**
     * 世論調査データとの関連
     */
    public function pollData(): HasMany
    {
        return $this->hasMany(PollData::class, 'party_id');
    }

    /**
     * 議席予測との関連
     */
    public function seatPredictions(): HasMany
    {
        return $this->hasMany(SeatPrediction::class, 'party_id');
    }

    /**
     * アクティブな政党のみ取得
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 特定日時点で存在した政党を取得
     */
    public function scopeExistedAt($query, $date)
    {
        return $query->where(function ($q) use ($date) {
            $q->whereNull('founded_date')
              ->orWhere('founded_date', '<=', $date);
        })->where(function ($q) use ($date) {
            $q->whereNull('dissolved_date')
              ->orWhere('dissolved_date', '>=', $date);
        });
    }
}
