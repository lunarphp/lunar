<?php

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Lunar\Facades\CartSession;
use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Tests\Core\Stubs\User;
use Lunar\Tests\Core\TestCase;

use function Pest\Laravel\actingAs;

uses(TestCase::class)->group('cart_session');
uses(RefreshDatabase::class);

test('cart is soft deleted on logout when delete_on_logout is true', function () {
    // Ensure required defaults exist
    Currency::factory()->create(['default' => true]);
    Channel::factory()->create(['default' => true]);

    // Create a session cart
    Config::set('lunar.cart_session.auto_create', true);

    $cart = CartSession::current();

    // Authenticate a Lunar user so the Logout listener will act on it
    $user = User::factory()->create();
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
    $user = User::factory()->create();
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

test('login restores user cart matching current channel only', function () {
    Currency::factory()->create(['default' => true]);
    $channelA = Channel::factory()->create(['default' => true]);
    $channelB = Channel::factory()->create(['default' => false]);

    $user = User::factory()->create();

    $cartB = Cart::factory()->create([
        'user_id' => $user->id,
        'channel_id' => $channelB->id,
    ]);

    $cartA = Cart::factory()->create([
        'user_id' => $user->id,
        'channel_id' => $channelA->id,
    ]);

    Config::set('lunar.cart_session.auto_create', false);

    // Trigger login
    event(new Login('web', $user, false));

    $currentCart = CartSession::current();

    expect($currentCart)->not->toBeNull()
        ->and($currentCart->id)->toBe($cartA->id)
        ->and($currentCart->channel_id)->toBe($channelA->id);
});
