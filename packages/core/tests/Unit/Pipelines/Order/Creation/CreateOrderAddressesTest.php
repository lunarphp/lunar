<?php

namespace Lunar\Tests\Unit\Pipelines\Order\Creation;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\Currency;
use Lunar\Models\Order;
use Lunar\Models\OrderAddress;
use Lunar\Pipelines\Order\Creation\CreateOrderAddresses;
use Lunar\Tests\TestCase;

/**
 * @group lunar.orders.pipelines
 */
class CreateOrderAddressesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_run_pipeline()
    {
        $currency = Currency::factory()->create();

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
        ]);

        CartAddress::factory()->create([
            'type' => 'billing',
            'cart_id' => $cart->id,
        ]);

        CartAddress::factory()->create([
            'type' => 'shipping',
            'cart_id' => $cart->id,
        ]);

        $order = Order::factory()->create([
            'cart_id' => $cart->id,
        ]);

        app(CreateOrderAddresses::class)->handle($order, function ($order) {
            return $order;
        });

        $this->assertCount($cart->addresses->count(), $order->addresses);
    }

    /** @test */
    public function can_update_existing_addresses()
    {
        $currency = Currency::factory()->create();

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
        ]);

        CartAddress::factory()->create([
            'type' => 'billing',
            'cart_id' => $cart->id,
            'postcode' => 'N1 1TW',
        ]);

        CartAddress::factory()->create([
            'type' => 'shipping',
            'cart_id' => $cart->id,
            'postcode' => 'N2 2TW',
        ]);

        $order = Order::factory()->create([
            'cart_id' => $cart->id,
        ]);

        OrderAddress::factory()->create([
            'type' => 'billing',
            'order_id' => $order->id,
            'postcode' => 'N1 1TW',
        ]);

        $address = OrderAddress::factory()->create([
            'type' => 'shipping',
            'order_id' => $order->id,
            'postcode' => 'N2 2TW',
        ]);

        app(CreateOrderAddresses::class)->handle($order, function ($order) {
            return $order;
        });

        $this->assertCount($cart->addresses->count(), $order->addresses);
    }
}
