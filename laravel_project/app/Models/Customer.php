<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'privacy_consent',
        'privacy_consent_date',
        'marketing_consent',
    ];

    // total_stays, total_spent は updateStayHistory() 経由でのみ更新
    // deletion_requested, deletion_requested_at は requestDeletion() 経由でのみ更新
    // last_stay_date は内部処理でのみ更新
    protected $guarded = [
        'total_stays',
        'total_spent',
        'last_stay_date',
        'deletion_requested',
        'deletion_requested_at',
    ];

    protected $casts = [
        'last_stay_date' => 'datetime',
        'total_spent' => 'decimal:2',
        'privacy_consent' => 'boolean',
        'privacy_consent_date' => 'datetime',
        'marketing_consent' => 'boolean',
        'deletion_requested' => 'boolean',
        'deletion_requested_at' => 'datetime',
    ];

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function preferences(): HasOne
    {
        return $this->hasOne(GuestPreference::class);
    }

    /**
     * 宿泊履歴を更新
     */
    public function updateStayHistory(float $amount): void
    {
        $this->total_stays += 1;
        $this->total_spent += $amount;
        $this->last_stay_date = now();
        $this->save();
    }

    /**
     * データ削除をリクエスト（GDPR対応）
     */
    public function requestDeletion(): void
    {
        $this->deletion_requested = true;
        $this->deletion_requested_at = now();
        $this->save();
    }
}
