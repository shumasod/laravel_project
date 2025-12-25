<?php

namespace App\Services;

use App\Models\PricingRule;
use App\Models\Room;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class PricingService
{
    /**
     * 宿泊料金を計算
     */
    public function calculateTotalPrice(
        Room $room,
        Carbon $checkIn,
        Carbon $checkOut,
        int $numberOfGuests = 1,
        ?Carbon $bookingDate = null
    ): array {
        $bookingDate = $bookingDate ?? now();
        $nights = $checkIn->diffInDays($checkOut);
        $period = CarbonPeriod::create($checkIn, $checkOut->subDay());

        $breakdown = [];
        $totalAmount = 0;
        $appliedDiscounts = [];

        // 日ごとの料金を計算
        foreach ($period as $date) {
            $dailyPrice = $this->calculateDailyPrice($room, $date, $numberOfGuests);
            $breakdown[$date->format('Y-m-d')] = $dailyPrice;
            $totalAmount += $dailyPrice['total'];
        }

        // 連泊割引
        $consecutiveDiscount = $this->applyConsecutiveNightsDiscount(
            $room->accommodation_id,
            $room->room_type,
            $totalAmount,
            $nights
        );
        if ($consecutiveDiscount) {
            $totalAmount -= $consecutiveDiscount['amount'];
            $appliedDiscounts[] = $consecutiveDiscount;
        }

        // 早割
        $earlyBirdDiscount = $this->applyEarlyBirdDiscount(
            $room->accommodation_id,
            $room->room_type,
            $totalAmount,
            $bookingDate,
            $checkIn
        );
        if ($earlyBirdDiscount) {
            $totalAmount -= $earlyBirdDiscount['amount'];
            $appliedDiscounts[] = $earlyBirdDiscount;
        }

        // 直前割
        $lastMinuteDiscount = $this->applyLastMinuteDiscount(
            $room->accommodation_id,
            $room->room_type,
            $totalAmount,
            $bookingDate,
            $checkIn
        );
        if ($lastMinuteDiscount) {
            $totalAmount -= $lastMinuteDiscount['amount'];
            $appliedDiscounts[] = $lastMinuteDiscount;
        }

        return [
            'total_amount' => round($totalAmount, 2),
            'base_amount' => array_sum(array_column($breakdown, 'base_price')),
            'nights' => $nights,
            'breakdown' => $breakdown,
            'applied_discounts' => $appliedDiscounts,
        ];
    }

    /**
     * 1日あたりの料金を計算
     */
    protected function calculateDailyPrice(Room $room, Carbon $date, int $numberOfGuests): array
    {
        $basePrice = (float) $room->price_per_night;
        $dailyTotal = $basePrice;
        $adjustments = [];

        // 曜日別料金
        $dayOfWeekAdjustment = $this->applyDayOfWeekPricing(
            $room->accommodation_id,
            $room->room_type,
            $date,
            $basePrice
        );
        if ($dayOfWeekAdjustment) {
            $dailyTotal = $dayOfWeekAdjustment['adjusted_price'];
            $adjustments[] = $dayOfWeekAdjustment;
        }

        // シーズン料金
        $seasonAdjustment = $this->applySeasonPricing(
            $room->accommodation_id,
            $room->room_type,
            $date,
            $dailyTotal
        );
        if ($seasonAdjustment) {
            $dailyTotal = $seasonAdjustment['adjusted_price'];
            $adjustments[] = $seasonAdjustment;
        }

        // 人数追加料金
        if ($numberOfGuests > $room->capacity) {
            $extraGuestCharge = $this->applyExtraGuestCharge(
                $room->accommodation_id,
                $room->room_type,
                $dailyTotal,
                $numberOfGuests - $room->capacity
            );
            if ($extraGuestCharge) {
                $dailyTotal += $extraGuestCharge['amount'];
                $adjustments[] = $extraGuestCharge;
            }
        }

        return [
            'date' => $date->format('Y-m-d'),
            'base_price' => $basePrice,
            'total' => round($dailyTotal, 2),
            'adjustments' => $adjustments,
        ];
    }

    /**
     * 曜日別料金を適用
     */
    protected function applyDayOfWeekPricing(
        int $accommodationId,
        string $roomType,
        Carbon $date,
        float $basePrice
    ): ?array {
        $rule = PricingRule::where('accommodation_id', $accommodationId)
            ->where('rule_type', 'day_of_week')
            ->where('is_active', true)
            ->where(function ($query) use ($roomType) {
                $query->where('room_type', $roomType)
                    ->orWhereNull('room_type');
            })
            ->where(function ($query) use ($date) {
                $query->where(function ($q) use ($date) {
                    $q->whereNull('valid_from')
                        ->orWhere('valid_from', '<=', $date);
                })
                ->where(function ($q) use ($date) {
                    $q->whereNull('valid_to')
                        ->orWhere('valid_to', '>=', $date);
                });
            })
            ->first();

        if (!$rule) {
            return null;
        }

        $conditions = $rule->conditions;
        $dayOfWeek = strtolower($date->format('l')); // monday, tuesday, etc.

        if (isset($conditions['days']) && in_array($dayOfWeek, $conditions['days'])) {
            $adjustedPrice = $rule->applyRule($basePrice);
            return [
                'type' => 'day_of_week',
                'name' => $rule->name,
                'adjusted_price' => $adjustedPrice,
                'difference' => $adjustedPrice - $basePrice,
            ];
        }

        return null;
    }

    /**
     * シーズン料金を適用
     */
    protected function applySeasonPricing(
        int $accommodationId,
        string $roomType,
        Carbon $date,
        float $currentPrice
    ): ?array {
        $rule = PricingRule::where('accommodation_id', $accommodationId)
            ->where('rule_type', 'season')
            ->where('is_active', true)
            ->where(function ($query) use ($roomType) {
                $query->where('room_type', $roomType)
                    ->orWhereNull('room_type');
            })
            ->where('valid_from', '<=', $date)
            ->where('valid_to', '>=', $date)
            ->orderBy('priority', 'desc')
            ->first();

        if (!$rule) {
            return null;
        }

        $adjustedPrice = $rule->applyRule($currentPrice);
        return [
            'type' => 'season',
            'name' => $rule->name,
            'adjusted_price' => $adjustedPrice,
            'difference' => $adjustedPrice - $currentPrice,
        ];
    }

    /**
     * 人数追加料金を適用
     */
    protected function applyExtraGuestCharge(
        int $accommodationId,
        string $roomType,
        float $currentPrice,
        int $extraGuests
    ): ?array {
        $rule = PricingRule::where('accommodation_id', $accommodationId)
            ->where('rule_type', 'extra_guest')
            ->where('is_active', true)
            ->where(function ($query) use ($roomType) {
                $query->where('room_type', $roomType)
                    ->orWhereNull('room_type');
            })
            ->first();

        if (!$rule) {
            return null;
        }

        $chargePerGuest = $rule->calculation_type === 'fixed'
            ? $rule->value
            : $currentPrice * ($rule->value / 100);

        $totalCharge = $chargePerGuest * $extraGuests;

        return [
            'type' => 'extra_guest',
            'name' => $rule->name,
            'amount' => $totalCharge,
            'extra_guests' => $extraGuests,
        ];
    }

    /**
     * 連泊割引を適用
     */
    protected function applyConsecutiveNightsDiscount(
        int $accommodationId,
        string $roomType,
        float $totalAmount,
        int $nights
    ): ?array {
        $rule = PricingRule::where('accommodation_id', $accommodationId)
            ->where('rule_type', 'consecutive_nights')
            ->where('is_active', true)
            ->where(function ($query) use ($roomType) {
                $query->where('room_type', $roomType)
                    ->orWhereNull('room_type');
            })
            ->first();

        if (!$rule) {
            return null;
        }

        $conditions = $rule->conditions;
        $minNights = $conditions['min_nights'] ?? 0;

        if ($nights < $minNights) {
            return null;
        }

        $discountAmount = $rule->calculation_type === 'percentage'
            ? $totalAmount * ($rule->value / 100)
            : $rule->value;

        return [
            'type' => 'consecutive_nights',
            'name' => $rule->name,
            'amount' => $discountAmount,
            'nights' => $nights,
        ];
    }

    /**
     * 早割を適用
     */
    protected function applyEarlyBirdDiscount(
        int $accommodationId,
        string $roomType,
        float $totalAmount,
        Carbon $bookingDate,
        Carbon $checkInDate
    ): ?array {
        $rule = PricingRule::where('accommodation_id', $accommodationId)
            ->where('rule_type', 'early_bird')
            ->where('is_active', true)
            ->where(function ($query) use ($roomType) {
                $query->where('room_type', $roomType)
                    ->orWhereNull('room_type');
            })
            ->first();

        if (!$rule) {
            return null;
        }

        $conditions = $rule->conditions;
        $minDaysInAdvance = $conditions['min_days_in_advance'] ?? 0;
        $daysInAdvance = $bookingDate->diffInDays($checkInDate);

        if ($daysInAdvance < $minDaysInAdvance) {
            return null;
        }

        $discountAmount = $rule->calculation_type === 'percentage'
            ? $totalAmount * ($rule->value / 100)
            : $rule->value;

        return [
            'type' => 'early_bird',
            'name' => $rule->name,
            'amount' => $discountAmount,
            'days_in_advance' => $daysInAdvance,
        ];
    }

    /**
     * 直前割を適用
     */
    protected function applyLastMinuteDiscount(
        int $accommodationId,
        string $roomType,
        float $totalAmount,
        Carbon $bookingDate,
        Carbon $checkInDate
    ): ?array {
        $rule = PricingRule::where('accommodation_id', $accommodationId)
            ->where('rule_type', 'last_minute')
            ->where('is_active', true)
            ->where(function ($query) use ($roomType) {
                $query->where('room_type', $roomType)
                    ->orWhereNull('room_type');
            })
            ->first();

        if (!$rule) {
            return null;
        }

        $conditions = $rule->conditions;
        $maxDaysInAdvance = $conditions['max_days_in_advance'] ?? 0;
        $daysInAdvance = $bookingDate->diffInDays($checkInDate);

        if ($daysInAdvance > $maxDaysInAdvance) {
            return null;
        }

        $discountAmount = $rule->calculation_type === 'percentage'
            ? $totalAmount * ($rule->value / 100)
            : $rule->value;

        return [
            'type' => 'last_minute',
            'name' => $rule->name,
            'amount' => $discountAmount,
            'days_in_advance' => $daysInAdvance,
        ];
    }
}
