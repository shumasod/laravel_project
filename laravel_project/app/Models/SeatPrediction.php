<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeatPrediction extends Model
{
    use HasFactory;

    protected $fillable = [
        'election_id',
        'party_id',
        'predicted_seats',
        'min_seats',
        'max_seats',
        'single_seat_prediction',
        'proportional_prediction',
        'confidence_level',
        'analysis_factors',
        'methodology',
        'predicted_at',
    ];

    protected $casts = [
        'predicted_seats' => 'integer',
        'min_seats' => 'integer',
        'max_seats' => 'integer',
        'single_seat_prediction' => 'integer',
        'proportional_prediction' => 'integer',
        'confidence_level' => 'decimal:2',
        'analysis_factors' => 'array',
        'predicted_at' => 'datetime',
    ];

    /**
     * 選挙との関連
     */
    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    /**
     * 政党との関連
     */
    public function party(): BelongsTo
    {
        return $this->belongsTo(PoliticalParty::class, 'party_id');
    }

    /**
     * 特定の選挙の最新予測を取得
     */
    public function scopeLatestForElection($query, $electionId)
    {
        return $query->where('election_id', $electionId)
            ->orderBy('predicted_at', 'desc');
    }

    /**
     * 予測範囲を取得
     */
    public function getPredictionRangeAttribute(): string
    {
        if ($this->min_seats && $this->max_seats) {
            return "{$this->min_seats} - {$this->max_seats}";
        }
        return (string) $this->predicted_seats;
    }

    /**
     * 予測精度（実績との比較）を計算
     */
    public function calculateAccuracy(): ?float
    {
        $actualResult = ElectionResult::where('election_id', $this->election_id)
            ->where('party_id', $this->party_id)
            ->sum('seats_won');

        if ($actualResult === 0 && $this->predicted_seats === 0) {
            return 100.0;
        }

        if ($actualResult === 0) {
            return 0.0;
        }

        $difference = abs($actualResult - $this->predicted_seats);
        $accuracy = max(0, 100 - ($difference / $actualResult * 100));

        return round($accuracy, 2);
    }
}
