<?php

uses(\Lunar\Tests\Core\TestCase::class)->group('cart_session');

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Lunar\Facades\CartSession;
use Lunar\Managers\CartSessionManager;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\Order;
use function Pest\Laravel\{actingAs, assertDatabaseMissing};

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

    Config::set('lunar.cart_session.auto_create', false);

    $cart = $manager->current();

    expect($cart)->toBeNull();

    Config::set('lunar.cart_session.auto_create', true);

    $cart = $manager->current();

    expect($cart)->toBeInstanceOf(Cart::class);

    $sessionCart = Session::get(config('lunar.cart_session.session_key'));

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

    Config::set('lunar.cart_session.auto_create', true);

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

    $sessionCart = Session::get(config('lunar.cart_session.session_key'));

    expect($sessionCart)->not->toBeNull();
    expect($sessionCart)->toEqual($cart->id);

    $order = CartSession::createOrder();

    expect($order)->toBeInstanceOf(Order::class);
    expect($cart->id)->toEqual($order->cart_id);

    expect(Session::get(config('lunar.cart_session.session_key')))->toBeNull();
});

test('can create order from session cart and retain cart', function () {
    Currency::factory()->create([
        'default' => true,
    ]);

    Channel::factory()->create([
        'default' => true,
    ]);

    Config::set('lunar.cart_session.auto_create', true);

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

    $sessionCart = Session::get(config('lunar.cart_session.session_key'));

    expect($sessionCart)->not->toBeNull();
    expect($sessionCart)->toEqual($cart->id);

    $order = CartSession::createOrder(
        forget: false
    );

    expect($order)->toBeInstanceOf(Order::class);
    expect($cart->id)->toEqual($order->cart_id);

    expect(Session::get(config('lunar.cart_session.session_key')))->toEqual($cart->id);
});

test('can fetch authenticated users cart and set in session', function () {
    Currency::factory()->create([
        'default' => true,
    ]);

    Channel::factory()->create([
        'default' => true,
    ]);

    Config::set('lunar.cart_session.auto_create', false);

    $cart = CartSession::current();

    $sessionCart = Session::get(config('lunar.cart_session.session_key'));

    expect($sessionCart)->toBeNull();
    expect($cart)->toBeNull();

    actingAs(
        $user = \Lunar\Tests\Core\Stubs\User::factory()->create()
    );

    $userCart = Cart::factory()->create([
        'user_id' => $user->id,
    ]);

    $cart = CartSession::current();
    $sessionCart = Session::get(config('lunar.cart_session.session_key'));

    expect($cart)->not->toBeNull();
    expect($cart->id)->toBe($userCart->id);
    expect($sessionCart)->not->toBeNull();
    expect($sessionCart)->toEqual($cart->id);

});

test('can forget a cart and soft delete it', function () {
    Currency::factory()->create([
        'default' => true,
    ]);

    Channel::factory()->create([
        'default' => true,
    ]);

    Config::set('lunar.cart_session.auto_create', true);

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

    $sessionCart = Session::get(config('lunar.cart_session.session_key'));

    expect($sessionCart)
        ->not
        ->toBeNull()
        ->and($sessionCart)
        ->toEqual($cart->id);

    CartSession::forget();

    expect(
        Session::get(config('lunar.cart_session.session_key'))
    )
        ->toBeNull()
        ->and($cart->refresh()->deleted_at)
        ->not
        ->toBeNull();

});

test('can forget a cart an optionally prevent soft deleting', function () {
    Currency::factory()->create([
        'default' => true,
    ]);

    Channel::factory()->create([
        'default' => true,
    ]);

    Config::set('lunar.cart_session.auto_create', true);

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

    $sessionCart = Session::get(config('lunar.cart_session.session_key'));

    expect($sessionCart)
        ->not
        ->toBeNull()
        ->and($sessionCart)
        ->toEqual($cart->id);

    CartSession::forget(delete: false);

    expect(
        Session::get(config('lunar.cart_session.session_key'))
    )
        ->toBeNull()
        ->and($cart->refresh()->deleted_at)
        ->toBeNull();

});

test('can set shipping estimate meta', function () {
    CartSession::estimateShippingUsing([
        'postcode' => 'NP1 1TX',
    ]);

    $meta = CartSession::getShippingEstimateMeta();
    expect($meta)->toBeArray();
    expect($meta['postcode'])->toEqual('NP1 1TX');
});

test('can return new instance when current cart has completed order', function () {
    Currency::factory()->create([
        'default' => true,
    ]);

    Channel::factory()->create([
        'default' => true,
    ]);

    Config::set('lunar.cart_session.auto_create', true);
    Config::set('lunar.cart_session.allow_multiple_orders_per_cart', false);

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

    $sessionCart = Session::get(config('lunar.cart_session.session_key'));

    expect($sessionCart)->not->toBeNull();
    expect($sessionCart)->toEqual($cart->id);

    $order = CartSession::createOrder(
        forget: false
    );

    expect($order)
        ->toBeInstanceOf(Order::class)
        ->and($cart->id)
        ->toEqual($order->cart_id)
        ->and(Session::get(config('lunar.cart_session.session_key')))
        ->toEqual($cart->id);

    $order->update([
        'placed_at' => now(),
    ]);

    $cart = CartSession::current()->calculate();

    expect($order->cart_id)->not->toBe($cart->id)
    ->and($cart->subTotal->value)->toBe(0)
    ->and(Session::get(config('lunar.cart_session.session_key')))
    ->toBe($cart->id);

    assertDatabaseMissing(Order::class, [
        'cart_id' => $cart->id,
    ]);
});
