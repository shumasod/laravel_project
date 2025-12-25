<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\InventoryService;
use App\Models\RoomInventory;
use App\Models\Accommodation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected InventoryService $inventoryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->inventoryService = new InventoryService();
    }

    public function test_can_initialize_inventory(): void
    {
        $accommodation = Accommodation::factory()->create();
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(7);

        $this->inventoryService->initializeInventory(
            $accommodation->id,
            'standard',
            $startDate,
            $endDate,
            10
        );

        $this->assertDatabaseHas('room_inventories', [
            'accommodation_id' => $accommodation->id,
            'room_type' => 'standard',
            'total_rooms' => 10,
            'available_rooms' => 10,
        ]);

        $count = RoomInventory::where('accommodation_id', $accommodation->id)
            ->where('room_type', 'standard')
            ->count();

        $this->assertEquals(8, $count); // 8日間分
    }

    public function test_can_check_availability(): void
    {
        $accommodation = Accommodation::factory()->create();
        $checkIn = Carbon::today()->addDays(1);
        $checkOut = Carbon::today()->addDays(3);

        $this->inventoryService->initializeInventory(
            $accommodation->id,
            'standard',
            $checkIn,
            $checkOut,
            5
        );

        $available = $this->inventoryService->checkAvailability(
            $accommodation->id,
            'standard',
            $checkIn,
            $checkOut,
            2
        );

        $this->assertTrue($available);

        $available = $this->inventoryService->checkAvailability(
            $accommodation->id,
            'standard',
            $checkIn,
            $checkOut,
            10
        );

        $this->assertFalse($available);
    }

    public function test_can_reserve_and_release_inventory(): void
    {
        $accommodation = Accommodation::factory()->create();
        $checkIn = Carbon::today()->addDays(1);
        $checkOut = Carbon::today()->addDays(3);

        $this->inventoryService->initializeInventory(
            $accommodation->id,
            'standard',
            $checkIn,
            $checkOut,
            10
        );

        // 予約
        $reserved = $this->inventoryService->reserveInventory(
            $accommodation->id,
            'standard',
            $checkIn,
            $checkOut,
            3
        );

        $this->assertTrue($reserved);

        $inventory = RoomInventory::where('accommodation_id', $accommodation->id)
            ->where('date', $checkIn)
            ->first();

        $this->assertEquals(7, $inventory->available_rooms);
        $this->assertEquals(3, $inventory->reserved_rooms);

        // 解放
        $this->inventoryService->releaseInventory(
            $accommodation->id,
            'standard',
            $checkIn,
            $checkOut,
            3
        );

        $inventory->refresh();
        $this->assertEquals(10, $inventory->available_rooms);
        $this->assertEquals(0, $inventory->reserved_rooms);
    }

    public function test_prevents_overbooking(): void
    {
        $accommodation = Accommodation::factory()->create();
        $checkIn = Carbon::today()->addDays(1);
        $checkOut = Carbon::today()->addDays(3);

        $this->inventoryService->initializeInventory(
            $accommodation->id,
            'standard',
            $checkIn,
            $checkOut,
            2
        );

        // 最初の予約
        $reserved1 = $this->inventoryService->reserveInventory(
            $accommodation->id,
            'standard',
            $checkIn,
            $checkOut,
            2
        );

        $this->assertTrue($reserved1);

        // オーバーブッキングを試みる
        $reserved2 = $this->inventoryService->reserveInventory(
            $accommodation->id,
            'standard',
            $checkIn,
            $checkOut,
            1
        );

        $this->assertFalse($reserved2);

        // 在庫が正しく維持されているか確認
        $inventory = RoomInventory::where('accommodation_id', $accommodation->id)
            ->where('date', $checkIn)
            ->first();

        $this->assertEquals(0, $inventory->available_rooms);
        $this->assertEquals(2, $inventory->reserved_rooms);
    }
}
