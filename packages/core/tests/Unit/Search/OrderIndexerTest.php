<?php

namespace Lunar\Tests\Unit\Search;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Currency;
use Lunar\Models\Order;
use Lunar\Search\OrderIndexer;
use Lunar\Tests\TestCase;

/**
 * @group lunar.search
 * @group lunar.search.order
 */
class OrderIndexerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_return_correct_searchable_data()
    {
        Currency::factory()->create([
            'code' => 'GBP',
            'default' => true,
        ]);

        $order = Order::factory()->create([
            'user_id' => null,
            'placed_at' => now(),
            'meta' => [
                'foo' => 'bar',
            ],
        ]);

        $data = app(OrderIndexer::class)->toSearchableArray($order);

        $this->assertEquals('GBP', $data['currency_code']);
        $this->assertEquals($order->channel->name, $data['channel']);
        $this->assertEquals($order->total->value, $data['total']);
    }
}
