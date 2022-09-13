<?php

namespace Lunar\Tests\Unit\Managers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Lunar\Facades\CartSession;
use Lunar\Managers\CartSessionManager;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\Order;
use Lunar\Tests\TestCase;

/**
 * @group lunar.cart-session-manager
 */
class CartSessionManagerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function can_instantiate_manager()
    {
        $manager = app(CartSessionManager::class);
        $this->assertInstanceOf(CartSessionManager::class, $manager);
    }

    /**
     * @test
     */
    public function can_fetch_current_cart()
    {
        $manager = app(CartSessionManager::class);

        Currency::factory()->create([
            'default' => true,
        ]);

        Channel::factory()->create([
            'default' => true,
        ]);

        Config::set('lunar.cart.auto_create', false);

        $cart = $manager->current();

        $this->assertNull($cart);

        Config::set('lunar.cart.auto_create', true);

        $cart = $manager->current();

        $this->assertInstanceOf(Cart::class, $cart);

        $sessionCart = Session::get(config('lunar.cart.session_key'));

        $this->assertNotNull($sessionCart);
        $this->assertEquals($cart->id, $sessionCart);
    }

    /**
     * @test
     */
    public function can_create_order_from_session_cart_and_cleanup()
    {
        Currency::factory()->create([
            'default' => true,
        ]);

        Channel::factory()->create([
            'default' => true,
        ]);

        Config::set('lunar.cart.auto_create', true);

        $cart = CartSession::current();

        $shipping = CartAddress::factory()->create([
            'cart_id' => $cart->id,
            'type' => 'shipping',
        ]);

        $billing = CartAddress::factory()->create([
            'cart_id' => $cart->id,
            'type' => 'billing',
        ]);

        $cart->getManager()->setShippingAddress($shipping);
        $cart->getManager()->setBillingAddress($billing);

        $sessionCart = Session::get(config('lunar.cart.session_key'));

        $this->assertNotNull($sessionCart);
        $this->assertEquals($cart->id, $sessionCart);

        $order = CartSession::createOrder();

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals($cart->order_id, $order->id);

        $this->assertNull(
            Session::get(config('lunar.cart.session_key'))
        );
    }

    /**
     * @test
     */
    public function can_create_order_from_session_cart_and_retain_cart()
    {
        Currency::factory()->create([
            'default' => true,
        ]);

        Channel::factory()->create([
            'default' => true,
        ]);

        Config::set('lunar.cart.auto_create', true);

        $cart = CartSession::current();

        $shipping = CartAddress::factory()->create([
            'cart_id' => $cart->id,
            'type' => 'shipping',
        ]);

        $billing = CartAddress::factory()->create([
            'cart_id' => $cart->id,
            'type' => 'billing',
        ]);

        $cart->getManager()->setShippingAddress($shipping);
        $cart->getManager()->setBillingAddress($billing);

        $sessionCart = Session::get(config('lunar.cart.session_key'));

        $this->assertNotNull($sessionCart);
        $this->assertEquals($cart->id, $sessionCart);

        $order = CartSession::createOrder(
            forget: false
        );

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals($cart->order_id, $order->id);

        $this->assertEquals(
            $cart->id,
            Session::get(config('lunar.cart.session_key'))
        );
    }
}
