<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\PricingRule;
use Carbon\Carbon;

class CheckInOutService
{
    /**
     * 標準チェックイン時刻（デフォルト: 15:00）
     */
    protected int $standardCheckInHour = 15;

    /**
     * 標準チェックアウト時刻（デフォルト: 11:00）
     */
    protected int $standardCheckOutHour = 11;

    /**
     * チェックイン処理
     */
    public function checkIn(Reservation $reservation, ?int $userId = null): array
    {
        $actualTime = now();
        $scheduledTime = $reservation->check_in_date->setTime($this->standardCheckInHour, 0);

        $isEarly = $actualTime->lt($scheduledTime);
        $extraCharge = 0;

        // アーリーチェックインの判定と追加料金
        if ($isEarly) {
            $hoursEarly = $scheduledTime->diffInHours($actualTime);
            $extraCharge = $this->calculateEarlyCheckInCharge(
                $reservation,
                $hoursEarly
            );
        }

        // チェックイン実行
        $success = $reservation->checkIn($userId);

        if ($success && $extraCharge > 0) {
            $reservation->total_amount += $extraCharge;
            $currentBreakdown = $reservation->price_breakdown ?? [];
            $currentBreakdown['early_check_in'] = [
                'hours_early' => $hoursEarly ?? 0,
                'charge' => $extraCharge,
            ];
            $reservation->price_breakdown = $currentBreakdown;
            $reservation->save();
        }

        return [
            'success' => $success,
            'actual_time' => $actualTime,
            'scheduled_time' => $scheduledTime,
            'is_early' => $isEarly,
            'extra_charge' => $extraCharge,
        ];
    }

    /**
     * チェックアウト処理
     */
    public function checkOut(Reservation $reservation, ?int $userId = null): array
    {
        $actualTime = now();
        $scheduledTime = $reservation->check_out_date->setTime($this->standardCheckOutHour, 0);

        $isLate = $actualTime->gt($scheduledTime);
        $extraCharge = 0;

        // レイトチェックアウトの判定と追加料金
        if ($isLate) {
            $hoursLate = $actualTime->diffInHours($scheduledTime);
            $extraCharge = $this->calculateLateCheckOutCharge(
                $reservation,
                $hoursLate
            );
        }

        // チェックアウト実行
        $success = $reservation->checkOut($userId);

        if ($success && $extraCharge > 0) {
            $reservation->total_amount += $extraCharge;
            $currentBreakdown = $reservation->price_breakdown ?? [];
            $currentBreakdown['late_check_out'] = [
                'hours_late' => $hoursLate ?? 0,
                'charge' => $extraCharge,
            ];
            $reservation->price_breakdown = $currentBreakdown;
            $reservation->save();
        }

        // 宿泊履歴を更新
        if ($success) {
            $reservation->customer->updateStayHistory((float) $reservation->total_amount);
        }

        return [
            'success' => $success,
            'actual_time' => $actualTime,
            'scheduled_time' => $scheduledTime,
            'is_late' => $isLate,
            'extra_charge' => $extraCharge,
        ];
    }

    /**
     * アーリーチェックイン料金を計算
     */
    protected function calculateEarlyCheckInCharge(Reservation $reservation, int $hoursEarly): float
    {
        $room = $reservation->room;

        // 料金ルールを取得
        $rule = PricingRule::where('accommodation_id', $room->accommodation_id)
            ->where('rule_type', 'early_check_in')
            ->where('is_active', true)
            ->first();

        if (!$rule) {
            // デフォルト: 1時間あたり基本料金の10%
            return ($room->price_per_night * 0.1) * $hoursEarly;
        }

        $conditions = $rule->conditions;
        $chargeableHours = max(0, $hoursEarly - ($conditions['free_hours'] ?? 0));

        if ($chargeableHours <= 0) {
            return 0;
        }

        return $rule->calculation_type === 'fixed'
            ? $rule->value * $chargeableHours
            : ($room->price_per_night * ($rule->value / 100)) * $chargeableHours;
    }

    /**
     * レイトチェックアウト料金を計算
     */
    protected function calculateLateCheckOutCharge(Reservation $reservation, int $hoursLate): float
    {
        $room = $reservation->room;

        // 料金ルールを取得
        $rule = PricingRule::where('accommodation_id', $room->accommodation_id)
            ->where('rule_type', 'late_check_out')
            ->where('is_active', true)
            ->first();

        if (!$rule) {
            // デフォルト: 1時間あたり基本料金の10%
            return ($room->price_per_night * 0.1) * $hoursLate;
        }

        $conditions = $rule->conditions;
        $chargeableHours = max(0, $hoursLate - ($conditions['free_hours'] ?? 0));

        if ($chargeableHours <= 0) {
            return 0;
        }

        // 一定時間を超えたら1泊分を請求
        $maxHoursBeforeFullDay = $conditions['max_hours_before_full_day'] ?? 6;
        if ($chargeableHours >= $maxHoursBeforeFullDay) {
            return (float) $room->price_per_night;
        }

        return $rule->calculation_type === 'fixed'
            ? $rule->value * $chargeableHours
            : ($room->price_per_night * ($rule->value / 100)) * $chargeableHours;
    }

    /**
     * 標準チェックイン時刻を設定
     */
    public function setStandardCheckInHour(int $hour): void
    {
        $this->standardCheckInHour = $hour;
    }

    /**
     * 標準チェックアウト時刻を設定
     */
    public function setStandardCheckOutHour(int $hour): void
    {
        $this->standardCheckOutHour = $hour;
    }
}
