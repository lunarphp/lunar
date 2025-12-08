<?php

use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Lunar\Facades\CartSession;
use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\Currency;

use function Pest\Laravel\actingAs;

uses(\Lunar\Tests\Core\TestCase::class)->group('cart_session');
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('cart is soft deleted on logout when delete_on_logout is true', function () {
    // Ensure required defaults exist
    Currency::factory()->create(['default' => true]);
    Channel::factory()->create(['default' => true]);

    // Create a session cart
    Config::set('lunar.cart_session.auto_create', true);

    $cart = CartSession::current();

    // Authenticate a Lunar user so the Logout listener will act on it
    $user = \Lunar\Tests\Core\Stubs\User::factory()->create();
    actingAs($user);

    // Sanity checks
    expect($cart)->toBeInstanceOf(Cart::class);
    expect(Session::get(config('lunar.cart_session.session_key')))->toEqual($cart->id);

    // Config dictates cart should be soft deleted on logout
    Config::set('lunar.cart_session.delete_on_forget', true);

    // Fire the logout event (this triggers CartSessionAuthListener@logout)
    event(new Logout('web', $user));

    // Session cart should be cleared and the cart soft deleted
    expect(Session::get(config('lunar.cart_session.session_key')))->toBeNull();
    expect($cart->refresh()->deleted_at)->not->toBeNull();
});

test('cart is not soft deleted on logout when delete_on_logout is false', function () {
    // Ensure required defaults exist
    Currency::factory()->create(['default' => true]);
    Channel::factory()->create(['default' => true]);

    // Create a session cart
    Config::set('lunar.cart_session.auto_create', true);

    $cart = CartSession::current();

    // Authenticate a Lunar user so the Logout listener will act on it
    $user = \Lunar\Tests\Core\Stubs\User::factory()->create();
    actingAs($user);

    // Sanity checks
    expect($cart)->toBeInstanceOf(Cart::class);
    expect(Session::get(config('lunar.cart_session.session_key')))->toEqual($cart->id);

    // Config dictates cart should NOT be soft deleted on logout
    Config::set('lunar.cart_session.delete_on_forget', false);

    // Fire the logout event (this triggers CartSessionAuthListener@logout)
    event(new Logout('web', $user));

    // Session cart should be cleared but the cart should remain not-deleted
    expect(Session::get(config('lunar.cart_session.session_key')))->toBeNull();
    expect($cart->refresh()->deleted_at)->toBeNull();
});
