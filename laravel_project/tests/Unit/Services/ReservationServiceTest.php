<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\ReservationService;
use App\Services\InventoryService;
use App\Services\PricingService;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Customer;
use App\Models\Accommodation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class ReservationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ReservationService $reservationService;
    protected InventoryService $inventoryService;
    protected PricingService $pricingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inventoryService = new InventoryService();
        $this->pricingService = new PricingService();
        $this->reservationService = new ReservationService(
            $this->inventoryService,
            $this->pricingService
        );
    }

    public function test_can_create_reservation(): void
    {
        $accommodation = Accommodation::factory()->create();
        $room = Room::factory()->create([
            'accommodation_id' => $accommodation->id,
            'room_type' => 'standard',
            'price_per_night' => 10000,
        ]);
        $customer = Customer::factory()->create();

        $checkIn = Carbon::today()->addDays(7);
        $checkOut = Carbon::today()->addDays(9);

        // 在庫を初期化
        $this->inventoryService->initializeInventory(
            $accommodation->id,
            'standard',
            $checkIn,
            $checkOut,
            10
        );

        $reservation = $this->reservationService->createReservation([
            'room_id' => $room->id,
            'customer_id' => $customer->id,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'number_of_guests' => 2,
        ]);

        $this->assertInstanceOf(Reservation::class, $reservation);
        $this->assertEquals(Reservation::STATUS_PROVISIONAL, $reservation->status);
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => Reservation::STATUS_PROVISIONAL,
        ]);
    }

    public function test_can_confirm_reservation(): void
    {
        $reservation = Reservation::factory()->create([
            'status' => Reservation::STATUS_PROVISIONAL,
        ]);

        $result = $this->reservationService->confirmReservation($reservation);

        $this->assertTrue($result);
        $this->assertEquals(Reservation::STATUS_CONFIRMED, $reservation->fresh()->status);
    }

    public function test_can_cancel_reservation(): void
    {
        $accommodation = Accommodation::factory()->create();
        $room = Room::factory()->create([
            'accommodation_id' => $accommodation->id,
            'room_type' => 'standard',
        ]);

        $reservation = Reservation::factory()->create([
            'room_id' => $room->id,
            'status' => Reservation::STATUS_CONFIRMED,
        ]);

        $result = $this->reservationService->cancelReservation(
            $reservation,
            'Customer request'
        );

        $this->assertTrue($result);
        $this->assertEquals(Reservation::STATUS_CANCELLED, $reservation->fresh()->status);
        $this->assertNotNull($reservation->fresh()->cancelled_at);
    }

    public function test_status_transition_rules_are_enforced(): void
    {
        $reservation = Reservation::factory()->create([
            'status' => Reservation::STATUS_CHECKED_OUT,
        ]);

        $result = $reservation->canTransitionTo(Reservation::STATUS_CONFIRMED);
        $this->assertFalse($result);

        $result = $reservation->changeStatus(Reservation::STATUS_CONFIRMED);
        $this->assertFalse($result);
    }
}
