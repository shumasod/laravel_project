<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class CancellationPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'rules',
    ];

    protected $casts = [
        'rules' => 'array',
    ];

    /**
     * このポリシーを使用するプラン
     */
    public function roomPlans(): HasMany
    {
        return $this->hasMany(RoomPlan::class);
    }

    /**
     * このポリシーを使用する宿泊施設
     */
    public function accommodations(): HasMany
    {
        return $this->hasMany(Accommodation::class);
    }

    /**
     * キャンセル料を計算
     */
    public function calculateCancellationFee(Carbon $checkInDate, int $totalAmount): array
    {
        $daysUntilCheckIn = Carbon::today()->diffInDays($checkInDate, false);
        $rules = $this->rules ?? [];

        // ルールを日数の降順でソート
        usort($rules, fn($a, $b) => ($b['days_before'] ?? 0) <=> ($a['days_before'] ?? 0));

        foreach ($rules as $rule) {
            $daysBefore = $rule['days_before'] ?? 0;
            $chargePercent = $rule['charge_percent'] ?? 100;

            if ($daysUntilCheckIn >= $daysBefore) {
                $fee = (int) round($totalAmount * ($chargePercent / 100));
                return [
                    'fee' => $fee,
                    'percent' => $chargePercent,
                    'refund' => $totalAmount - $fee,
                    'days_until_check_in' => $daysUntilCheckIn,
                    'rule_applied' => $rule,
                ];
            }
        }

        // どのルールにも該当しない場合は全額
        return [
            'fee' => $totalAmount,
            'percent' => 100,
            'refund' => 0,
            'days_until_check_in' => $daysUntilCheckIn,
            'rule_applied' => null,
        ];
    }

    /**
     * 無料キャンセル期限を取得
     */
    public function getFreeCancellationDeadline(Carbon $checkInDate): ?Carbon
    {
        $rules = $this->rules ?? [];

        foreach ($rules as $rule) {
            if (($rule['charge_percent'] ?? 100) === 0) {
                $daysBefore = $rule['days_before'] ?? 0;
                return $checkInDate->copy()->subDays($daysBefore);
            }
        }

        return null;
    }

    /**
     * ポリシーの説明文を生成
     */
    public function getPolicyDescriptionAttribute(): string
    {
        $rules = $this->rules ?? [];
        $descriptions = [];

        usort($rules, fn($a, $b) => ($b['days_before'] ?? 0) <=> ($a['days_before'] ?? 0));

        foreach ($rules as $rule) {
            $daysBefore = $rule['days_before'] ?? 0;
            $chargePercent = $rule['charge_percent'] ?? 100;

            if ($chargePercent === 0) {
                $descriptions[] = "{$daysBefore}日前まで無料";
            } elseif ($chargePercent === 100) {
                $descriptions[] = "{$daysBefore}日前以降は全額";
            } else {
                $descriptions[] = "{$daysBefore}日前以降は{$chargePercent}%";
            }
        }

        return implode(' / ', $descriptions);
    }
}
