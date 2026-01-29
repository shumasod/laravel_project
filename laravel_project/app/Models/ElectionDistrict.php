<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ElectionDistrict extends Model
{
    use HasFactory;

    // 選挙区タイプ定数
    const TYPE_SINGLE_SEAT = 'single_seat'; // 小選挙区
    const TYPE_PROPORTIONAL = 'proportional'; // 比例代表

    // 議院タイプ
    const HOUSE_OF_REPRESENTATIVES = 'house_of_representatives'; // 衆議院
    const HOUSE_OF_COUNCILLORS = 'house_of_councillors'; // 参議院

    protected $fillable = [
        'name',
        'prefecture',
        'type',
        'house_type',
        'seats',
        'registered_voters',
        'municipalities',
        'is_active',
    ];

    protected $casts = [
        'seats' => 'integer',
        'registered_voters' => 'integer',
        'municipalities' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * 選挙結果との関連
     */
    public function electionResults(): HasMany
    {
        return $this->hasMany(ElectionResult::class, 'district_id');
    }

    /**
     * 小選挙区のみ取得
     */
    public function scopeSingleSeat($query)
    {
        return $query->where('type', self::TYPE_SINGLE_SEAT);
    }

    /**
     * 比例代表のみ取得
     */
    public function scopeProportional($query)
    {
        return $query->where('type', self::TYPE_PROPORTIONAL);
    }

    /**
     * 衆議院の選挙区のみ取得
     */
    public function scopeHouseOfRepresentatives($query)
    {
        return $query->where('house_type', self::HOUSE_OF_REPRESENTATIVES);
    }

    /**
     * 参議院の選挙区のみ取得
     */
    public function scopeHouseOfCouncillors($query)
    {
        return $query->where('house_type', self::HOUSE_OF_COUNCILLORS);
    }

    /**
     * 都道府県でフィルタ
     */
    public function scopeInPrefecture($query, $prefecture)
    {
        return $query->where('prefecture', $prefecture);
    }

    /**
     * アクティブな選挙区のみ取得
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 選挙区タイプの日本語名を取得
     */
    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            self::TYPE_SINGLE_SEAT => '小選挙区',
            self::TYPE_PROPORTIONAL => '比例代表',
            default => '不明',
        };
    }
}
