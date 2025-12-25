<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PricingService;
use App\Models\Room;
use App\Models\Accommodation;
use App\Models\PricingRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class PricingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PricingService $pricingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pricingService = new PricingService();
    }

    public function test_calculates_basic_price(): void
    {
        $accommodation = Accommodation::factory()->create();
        $room = Room::factory()->create([
            'accommodation_id' => $accommodation->id,
            'price_per_night' => 10000,
            'capacity' => 2,
        ]);

        $checkIn = Carbon::today()->addDays(7);
        $checkOut = Carbon::today()->addDays(9);

        $result = $this->pricingService->calculateTotalPrice(
            $room,
            $checkIn,
            $checkOut,
            2
        );

        $this->assertEquals(20000, $result['total_amount']);
        $this->assertEquals(2, $result['nights']);
    }

    public function test_applies_weekend_pricing(): void
    {
        $accommodation = Accommodation::factory()->create();
        $room = Room::factory()->create([
            'accommodation_id' => $accommodation->id,
            'price_per_night' => 10000,
            'room_type' => 'standard',
        ]);

        // 週末料金ルールを作成（20%増）
        PricingRule::create([
            'accommodation_id' => $accommodation->id,
            'room_type' => 'standard',
            'rule_type' => 'day_of_week',
            'name' => '週末料金',
            'conditions' => [
                'days' => ['friday', 'saturday']
            ],
            'calculation_type' => 'percentage',
            'value' => 20,
            'is_active' => true,
        ]);

        // 金曜日から日曜日まで
        $checkIn = Carbon::parse('next friday');
        $checkOut = $checkIn->copy()->addDays(2);

        $result = $this->pricingService->calculateTotalPrice(
            $room,
            $checkIn,
            $checkOut,
            2
        );

        // 金曜日と土曜日は20%増、日曜日は通常料金
        $expectedFriday = 10000 * 1.2;
        $expectedSaturday = 10000 * 1.2;
        $expected = $expectedFriday + $expectedSaturday;

        $this->assertEquals($expected, $result['total_amount']);
    }

    public function test_applies_consecutive_nights_discount(): void
    {
        $accommodation = Accommodation::factory()->create();
        $room = Room::factory()->create([
            'accommodation_id' => $accommodation->id,
            'price_per_night' => 10000,
            'room_type' => 'standard',
        ]);

        // 連泊割引ルール（3泊以上で10%オフ）
        PricingRule::create([
            'accommodation_id' => $accommodation->id,
            'room_type' => 'standard',
            'rule_type' => 'consecutive_nights',
            'name' => '連泊割引',
            'conditions' => [
                'min_nights' => 3
            ],
            'calculation_type' => 'percentage',
            'value' => 10,
            'is_active' => true,
        ]);

        $checkIn = Carbon::today()->addDays(7);
        $checkOut = Carbon::today()->addDays(10); // 3泊

        $result = $this->pricingService->calculateTotalPrice(
            $room,
            $checkIn,
            $checkOut,
            2
        );

        $baseAmount = 10000 * 3;
        $expectedDiscount = $baseAmount * 0.1;
        $expected = $baseAmount - $expectedDiscount;

        $this->assertEquals($expected, $result['total_amount']);
        $this->assertNotEmpty($result['applied_discounts']);
    }

    public function test_applies_early_bird_discount(): void
    {
        $accommodation = Accommodation::factory()->create();
        $room = Room::factory()->create([
            'accommodation_id' => $accommodation->id,
            'price_per_night' => 10000,
            'room_type' => 'standard',
        ]);

        // 早割ルール（30日前予約で15%オフ）
        PricingRule::create([
            'accommodation_id' => $accommodation->id,
            'room_type' => 'standard',
            'rule_type' => 'early_bird',
            'name' => '早割30',
            'conditions' => [
                'min_days_in_advance' => 30
            ],
            'calculation_type' => 'percentage',
            'value' => 15,
            'is_active' => true,
        ]);

        $bookingDate = Carbon::today();
        $checkIn = Carbon::today()->addDays(35);
        $checkOut = $checkIn->copy()->addDays(2);

        $result = $this->pricingService->calculateTotalPrice(
            $room,
            $checkIn,
            $checkOut,
            2,
            $bookingDate
        );

        $baseAmount = 10000 * 2;
        $expectedDiscount = $baseAmount * 0.15;
        $expected = $baseAmount - $expectedDiscount;

        $this->assertEquals($expected, $result['total_amount']);
    }
}
