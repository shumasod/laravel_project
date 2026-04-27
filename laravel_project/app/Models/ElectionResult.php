<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElectionResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'election_id',
        'district_id',
        'party_id',
        'candidate_name',
        'votes',
        'vote_share',
        'seats_won',
        'is_winner',
        'rank',
        'notes',
    ];

    protected $casts = [
        'votes' => 'integer',
        'vote_share' => 'decimal:2',
        'seats_won' => 'integer',
        'is_winner' => 'boolean',
        'rank' => 'integer',
    ];

    /**
     * 選挙との関連
     */
    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    /**
     * 選挙区との関連
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(ElectionDistrict::class, 'district_id');
    }

    /**
     * 政党との関連
     */
    public function party(): BelongsTo
    {
        return $this->belongsTo(PoliticalParty::class, 'party_id');
    }

    /**
     * 当選者のみ取得
     */
    public function scopeWinners($query)
    {
        return $query->where('is_winner', true);
    }

    /**
     * 特定の政党の結果を取得
     */
    public function scopeForParty($query, $partyId)
    {
        return $query->where('party_id', $partyId);
    }

    /**
     * 議席を獲得した結果のみ取得
     */
    public function scopeWithSeats($query)
    {
        return $query->where('seats_won', '>', 0);
    }
}
