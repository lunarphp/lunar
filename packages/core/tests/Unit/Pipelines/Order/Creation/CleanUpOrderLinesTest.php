<?php

namespace Lunar\Tests\Unit\Pipelines\Order\Creation;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\DataTypes\Price;
use Lunar\DataTypes\ShippingOption;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\Currency;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Pipelines\Order\Creation\CleanUpOrderLines;
use Lunar\Tests\TestCase;

/**
 * @group lunar.orders.pipelines
 */
class CleanUpOrderLinesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_run_pipeline()
    {
        $currency = Currency::factory()->create();

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
        ]);

        ShippingManifest::addOption(
            new ShippingOption(
                name: 'Basic Delivery',
                description: 'Basic Delivery',
                identifier: 'BASDEL',
                price: new Price(500, $cart->currency, 1),
                taxClass: TaxClass::factory()->create()
            )
        );

        CartAddress::factory()->create([
            'type' => 'shipping',
            'shipping_option' => 'BASDEL',
            'cart_id' => $cart->id,
        ]);

        $order = Order::factory()->create([
            'cart_id' => $cart->id,
        ]);

        $purchasable = ProductVariant::factory()->create();
        $purchasableB = ProductVariant::factory()->create();

        \Lunar\Models\Price::factory()->create([
            'price' => 100,
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasable),
            'priceable_id' => $purchasable->id,
        ]);

        \Lunar\Models\Price::factory()->create([
            'price' => 100,
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableB),
            'priceable_id' => $purchasableB->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasable),
            'purchasable_id' => $purchasable->id,
            'quantity' => 1,
        ]);

        OrderLine::factory()->create([
            'order_id' => $order->id,
            'purchasable_id' => $purchasable->id,
            'purchasable_type' => get_class($purchasable),
        ]);

        OrderLine::factory()->create([
            'order_id' => $order->id,
            'purchasable_id' => $purchasableB->id,
            'purchasable_type' => get_class($purchasableB),
        ]);

        OrderLine::factory()->create([
            'identifier' => 'BASDEL',
            'purchasable_type' => ShippingOption::class,
            'type' => 'shipping',
            'order_id' => $order->id,
        ]);

        $order = app(CleanUpOrderLines::class)->handle($order, function ($order) {
            return $order;
        });

        $this->assertCount(1, $order->productLines);
        $this->assertEquals('BASDEL', $order->shippingLines->first()->identifier);
    }
}
