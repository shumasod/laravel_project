<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PollData extends Model
{
    use HasFactory;

    // 調査方法の定数
    const POLL_TYPE_PHONE = 'phone'; // 電話調査
    const POLL_TYPE_ONLINE = 'online'; // オンライン調査
    const POLL_TYPE_EXIT_POLL = 'exit_poll'; // 出口調査
    const POLL_TYPE_MIXED = 'mixed'; // 混合調査

    // 主要メディアソース
    const SOURCE_NHK = 'NHK';
    const SOURCE_YOMIURI = '読売新聞';
    const SOURCE_ASAHI = '朝日新聞';
    const SOURCE_MAINICHI = '毎日新聞';
    const SOURCE_NIKKEI = '日本経済新聞';
    const SOURCE_SANKEI = '産経新聞';
    const SOURCE_KYODO = '共同通信';
    const SOURCE_JIJI = '時事通信';

    protected $table = 'poll_data';

    protected $fillable = [
        'party_id',
        'election_id',
        'source',
        'poll_type',
        'survey_start_date',
        'survey_end_date',
        'support_rate',
        'margin_of_error',
        'sample_size',
        'response_rate',
        'demographic_breakdown',
        'regional_breakdown',
        'notes',
    ];

    protected $casts = [
        'survey_start_date' => 'date',
        'survey_end_date' => 'date',
        'support_rate' => 'decimal:2',
        'margin_of_error' => 'decimal:2',
        'sample_size' => 'integer',
        'response_rate' => 'decimal:2',
        'demographic_breakdown' => 'array',
        'regional_breakdown' => 'array',
    ];

    /**
     * 政党との関連
     */
    public function party(): BelongsTo
    {
        return $this->belongsTo(PoliticalParty::class, 'party_id');
    }

    /**
     * 選挙との関連
     */
    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    /**
     * 特定のソースでフィルタ
     */
    public function scopeFromSource($query, $source)
    {
        return $query->where('source', $source);
    }

    /**
     * 特定期間の調査を取得
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('survey_end_date', [$startDate, $endDate]);
    }

    /**
     * 特定の調査方法でフィルタ
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('poll_type', $type);
    }

    /**
     * 調査方法の日本語名を取得
     */
    public function getPollTypeNameAttribute(): string
    {
        return match($this->poll_type) {
            self::POLL_TYPE_PHONE => '電話調査',
            self::POLL_TYPE_ONLINE => 'オンライン調査',
            self::POLL_TYPE_EXIT_POLL => '出口調査',
            self::POLL_TYPE_MIXED => '混合調査',
            default => '不明',
        };
    }

    /**
     * 利用可能なソースの一覧を取得
     */
    public static function getAvailableSources(): array
    {
        return [
            self::SOURCE_NHK,
            self::SOURCE_YOMIURI,
            self::SOURCE_ASAHI,
            self::SOURCE_MAINICHI,
            self::SOURCE_NIKKEI,
            self::SOURCE_SANKEI,
            self::SOURCE_KYODO,
            self::SOURCE_JIJI,
        ];
    }
}
