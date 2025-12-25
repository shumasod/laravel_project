<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_check_status_transition_validity(): void
    {
        $reservation = Reservation::factory()->create([
            'status' => Reservation::STATUS_PROVISIONAL,
        ]);

        $this->assertTrue($reservation->canTransitionTo(Reservation::STATUS_CONFIRMED));
        $this->assertTrue($reservation->canTransitionTo(Reservation::STATUS_CANCELLED));
        $this->assertFalse($reservation->canTransitionTo(Reservation::STATUS_CHECKED_IN));
    }

    public function test_can_transition_status(): void
    {
        $reservation = Reservation::factory()->create([
            'status' => Reservation::STATUS_PROVISIONAL,
        ]);

        $result = $reservation->changeStatus(Reservation::STATUS_CONFIRMED);

        $this->assertTrue($result);
        $this->assertEquals(Reservation::STATUS_CONFIRMED, $reservation->status);
        $this->assertDatabaseHas('reservation_status_histories', [
            'reservation_id' => $reservation->id,
            'from_status' => Reservation::STATUS_PROVISIONAL,
            'to_status' => Reservation::STATUS_CONFIRMED,
        ]);
    }

    public function test_cannot_transition_to_invalid_status(): void
    {
        $reservation = Reservation::factory()->create([
            'status' => Reservation::STATUS_PROVISIONAL,
        ]);

        $result = $reservation->changeStatus(Reservation::STATUS_CHECKED_IN);

        $this->assertFalse($result);
        $this->assertEquals(Reservation::STATUS_PROVISIONAL, $reservation->status);
    }

    public function test_can_check_in(): void
    {
        $reservation = Reservation::factory()->create([
            'status' => Reservation::STATUS_CONFIRMED,
        ]);

        $result = $reservation->checkIn();

        $this->assertTrue($result);
        $this->assertEquals(Reservation::STATUS_CHECKED_IN, $reservation->fresh()->status);
        $this->assertNotNull($reservation->fresh()->actual_check_in_time);
    }

    public function test_can_check_out(): void
    {
        $reservation = Reservation::factory()->create([
            'status' => Reservation::STATUS_CHECKED_IN,
        ]);

        $result = $reservation->checkOut();

        $this->assertTrue($result);
        $this->assertEquals(Reservation::STATUS_CHECKED_OUT, $reservation->fresh()->status);
        $this->assertNotNull($reservation->fresh()->actual_check_out_time);
    }

    public function test_calculates_number_of_nights(): void
    {
        $reservation = Reservation::factory()->create([
            'check_in_date' => now()->addDays(1),
            'check_out_date' => now()->addDays(4),
        ]);

        $this->assertEquals(3, $reservation->getNumberOfNights());
    }
}
