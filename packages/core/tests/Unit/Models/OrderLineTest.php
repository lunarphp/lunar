<?php

namespace Lunar\Tests\Unit\Models;

use Lunar\Exceptions\NonPurchasableItemException;
use Lunar\Models\CartLine;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;
use Lunar\Models\ProductVariant;
use Lunar\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group lunar.orderlines
 */
class OrderLineTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_make_an_order_line()
    {
        $order = Order::factory()->create();

        Currency::factory()->create([
            'default' => true,
        ]);

        $data = [
            'order_id'         => $order->id,
            'quantity'         => 1,
            'purchasable_type' => ProductVariant::class,
            'purchasable_id'   => ProductVariant::factory()->create()->id,
        ];

        OrderLine::factory()->create($data);

        $this->assertDatabaseHas(
            (new OrderLine())->getTable(),
            $data
        );
    }

    /** @test */
    public function only_purchasables_can_be_added_to_an_order()
    {
        $order = Order::factory()->create();

        $this->expectException(NonPurchasableItemException::class);

        $data = [
            'order_id'         => $order->id,
            'quantity'         => 1,
            'purchasable_type' => Channel::class,
            'purchasable_id'   => Channel::factory()->create()->id,
        ];

        OrderLine::factory()->create($data);

        $this->assertDatabaseMissing((new CartLine())->getTable(), $data);
    }
}
