<?php

namespace Lunar\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\StockReservation;
use Lunar\Tests\TestCase;

/**
 * @group lunar.stockreservation
 */
class StockReservationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_make_a_stock_reservation()
    {
        $stockReservation = StockReservation::factory()->create([
            'quantity' => 5,
        ]);
        $this->assertEquals(5, $stockReservation->quantity);
    }
}
