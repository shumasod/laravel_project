<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Election extends Model
{
    use HasFactory;

    // 選挙タイプ定数
    const TYPE_HOUSE_OF_REPRESENTATIVES = 'house_of_representatives'; // 衆議院
    const TYPE_HOUSE_OF_COUNCILLORS = 'house_of_councillors'; // 参議院

    protected $fillable = [
        'name',
        'type',
        'election_date',
        'announcement_date',
        'total_seats',
        'single_seat_districts',
        'proportional_seats',
        'voter_turnout',
        'total_voters',
        'total_votes',
        'notes',
    ];

    protected $casts = [
        'election_date' => 'date',
        'announcement_date' => 'date',
        'total_seats' => 'integer',
        'single_seat_districts' => 'integer',
        'proportional_seats' => 'integer',
        'voter_turnout' => 'decimal:2',
        'total_voters' => 'integer',
        'total_votes' => 'integer',
    ];

    /**
     * 選挙結果との関連
     */
    public function results(): HasMany
    {
        return $this->hasMany(ElectionResult::class);
    }

    /**
     * 世論調査データとの関連
     */
    public function pollData(): HasMany
    {
        return $this->hasMany(PollData::class);
    }

    /**
     * 議席予測との関連
     */
    public function seatPredictions(): HasMany
    {
        return $this->hasMany(SeatPrediction::class);
    }

    /**
     * 衆議院選挙のみ取得
     */
    public function scopeHouseOfRepresentatives($query)
    {
        return $query->where('type', self::TYPE_HOUSE_OF_REPRESENTATIVES);
    }

    /**
     * 参議院選挙のみ取得
     */
    public function scopeHouseOfCouncillors($query)
    {
        return $query->where('type', self::TYPE_HOUSE_OF_COUNCILLORS);
    }

    /**
     * 特定期間の選挙を取得
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('election_date', [$startDate, $endDate]);
    }

    /**
     * 選挙タイプの日本語名を取得
     */
    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            self::TYPE_HOUSE_OF_REPRESENTATIVES => '衆議院議員総選挙',
            self::TYPE_HOUSE_OF_COUNCILLORS => '参議院議員通常選挙',
            default => '不明',
        };
    }

    /**
     * 政党別の獲得議席数を取得
     */
    public function getSeatsByParty(): array
    {
        return $this->results()
            ->selectRaw('party_id, SUM(seats_won) as total_seats')
            ->groupBy('party_id')
            ->with('party:id,name,short_name,color')
            ->get()
            ->mapWithKeys(function ($result) {
                return [$result->party->name => $result->total_seats];
            })
            ->toArray();
    }
}
