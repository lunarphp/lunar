<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Lunar\Facades\CartSession;
use Lunar\Managers\CartSessionManager;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\Order;

use function Pest\Laravel\{actingAs};

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can instantiate manager', function () {
    $manager = app(CartSessionManager::class);
    expect($manager)->toBeInstanceOf(CartSessionManager::class);
});

test('can fetch current cart', function () {
    $manager = app(CartSessionManager::class);

    Currency::factory()->create([
        'default' => true,
    ]);

    Channel::factory()->create([
        'default' => true,
    ]);

    Config::set('lunar.cart.auto_create', false);

    $cart = $manager->current();

    expect($cart)->toBeNull();

    Config::set('lunar.cart.auto_create', true);

    $cart = $manager->current();

    expect($cart)->toBeInstanceOf(Cart::class);

    $sessionCart = Session::get(config('lunar.cart.session_key'));

    expect($sessionCart)->not->toBeNull();
    expect($sessionCart)->toEqual($cart->id);
});

test('can create order from session cart and cleanup', function () {
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

    $cart->setShippingAddress($shipping);
    $cart->setBillingAddress($billing);

    $sessionCart = Session::get(config('lunar.cart.session_key'));

    expect($sessionCart)->not->toBeNull();
    expect($sessionCart)->toEqual($cart->id);

    $order = CartSession::createOrder();

    expect($order)->toBeInstanceOf(Order::class);
    expect($cart->id)->toEqual($order->cart_id);

    expect(Session::get(config('lunar.cart.session_key')))->toBeNull();
});

test('can create order from session cart and retain cart', function () {
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

    $cart->setShippingAddress($shipping);
    $cart->setBillingAddress($billing);

    $sessionCart = Session::get(config('lunar.cart.session_key'));

    expect($sessionCart)->not->toBeNull();
    expect($sessionCart)->toEqual($cart->id);

    $order = CartSession::createOrder(
        forget: false
    );

    expect($order)->toBeInstanceOf(Order::class);
    expect($cart->id)->toEqual($order->cart_id);

    expect(Session::get(config('lunar.cart.session_key')))->toEqual($cart->id);
});

test('can fetch authenticated users cart and set in session', function () {
    Currency::factory()->create([
        'default' => true,
    ]);

    Channel::factory()->create([
        'default' => true,
    ]);

    Config::set('lunar.cart.auto_create', false);

    $cart = CartSession::current();

    $sessionCart = Session::get(config('lunar.cart.session_key'));

    expect($sessionCart)->toBeNull();
    expect($cart)->toBeNull();

    actingAs(
        $user = \Lunar\Tests\Core\Stubs\User::factory()->create()
    );

    $userCart = Cart::factory()->create([
        'user_id' => $user->id,
    ]);

    $cart = CartSession::current();
    $sessionCart = Session::get(config('lunar.cart.session_key'));

    expect($cart)->not->toBeNull();
    expect($cart->id)->toBe($userCart->id);
    expect($sessionCart)->not->toBeNull();
    expect($sessionCart)->toEqual($cart->id);

});

test('can set shipping estimate meta', function () {
    CartSession::estimateShippingUsing([
        'postcode' => 'NP1 1TX',
    ]);

    $meta = CartSession::getShippingEstimateMeta();
    expect($meta)->toBeArray();
    expect($meta['postcode'])->toEqual('NP1 1TX');
});
