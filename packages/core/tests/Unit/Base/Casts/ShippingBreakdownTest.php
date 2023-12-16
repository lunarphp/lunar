<?php

namespace Lunar\Tests\Unit\Base\Casts;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Base\Casts\ShippingBreakdown as ShippingBreakdownCasts;
use Lunar\Base\ValueObjects\Cart\ShippingBreakdown;
use Lunar\Base\ValueObjects\Cart\ShippingBreakdownItem;
use Lunar\DataTypes\Price;
use Lunar\Models\Currency;
use Lunar\Models\Order;
use Lunar\Tests\TestCase;

/**
 * @group model.casts
 */
class ShippingBreakdownTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_set_from_value_object()
    {
        $currency = Currency::factory()->create();
        $order = Order::factory()->create();

        $shippingBreakdownValueObject = new ShippingBreakdown();

        $shippingBreakdownValueObject->items->put('DELIV',
            new ShippingBreakdownItem(
                name: 'Basic Delivery',
                identifier: 'DELIV',
                price: new Price(700, $currency, 1),
            )
        );

        $breakDown = new ShippingBreakdownCasts;

        $result = $breakDown->set($order, 'shipping_breakdown', $shippingBreakdownValueObject, []);

        $this->assertArrayHasKey('shipping_breakdown', $result);
        $this->assertJson($result['shipping_breakdown']);
    }

    /** @test */
    public function can_cast_to_and_from_model()
    {
        $currency = Currency::factory()->create();
        $order = Order::factory()->create();

        $shippingBreakdownValueObject = new ShippingBreakdown();

        $shippingBreakdownValueObject->items->put('DELIV',
            new ShippingBreakdownItem(
                name: 'Basic Delivery',
                identifier: 'DELIV',
                price: new Price(700, $currency, 1),
            )
        );

        $order->update([
            'shipping_breakdown' => $shippingBreakdownValueObject,
        ]);

        $breakdown = $order->refresh()->shipping_breakdown;
        $this->assertInstanceOf(ShippingBreakdown::class, $breakdown);
    }
}
