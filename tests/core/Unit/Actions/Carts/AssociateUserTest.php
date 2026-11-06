<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Actions\Carts\AssociateUser;
use Lunar\Facades\CartSession;
use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\Order;
use Lunar\Tests\Core\Stubs\User;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('can associate a user', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $this->assertDatabaseHas((new Cart)->getTable(), [
        'user_id' => null,
        'id' => $cart->id,
        'merged_id' => null,
    ]);

    $action = new AssociateUser;

    $user = User::factory()->create();
    $action->execute($cart, $user);

    $this->assertDatabaseHas((new Cart)->getTable(), [
        'user_id' => $user->id,
        'id' => $cart->id,
        'merged_id' => null,
    ]);
});

test('can associate a user with a customer', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $this->assertDatabaseHas((new Cart)->getTable(), [
        'user_id' => null,
        'id' => $cart->id,
        'merged_id' => null,
    ]);

    $action = new AssociateUser;

    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    $user->customers()->attach($customer);

    $action->execute($cart, $user);

    $this->assertDatabaseHas((new Cart)->getTable(), [
        'user_id' => $user->id,
        'customer_id' => $customer->id,
        'id' => $cart->id,
        'merged_id' => null,
    ]);
});

test('cant associate user to cart with order', function () {
    $currency = Currency::factory()->create();

    $user = User::factory()->create();

    $userCart = Cart::factory()->create([
        'user_id' => $user->id,
        'currency_id' => $currency->id,
    ]);

    Order::factory()->create([
        'placed_at' => now(),
        'cart_id' => $userCart->id,
    ]);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $this->assertDatabaseHas((new Cart)->getTable(), [
        'user_id' => null,
        'id' => $cart->id,
        'merged_id' => null,
    ]);

    $this->assertDatabaseHas((new Cart)->getTable(), [
        'user_id' => $user->id,
        'id' => $userCart->id,
        'merged_id' => null,
    ]);

    $action = new AssociateUser;

    $action->execute($cart, $user);

    $this->assertDatabaseHas((new Cart)->getTable(), [
        'user_id' => $user->id,
        'id' => $cart->id,
        'merged_id' => null,
    ]);
});

test('does not merge a user cart from a different channel', function () {
    $currency = Currency::factory()->create([
        'default' => true,
    ]);

    $channelA = Channel::factory()->create([
        'default' => true,
    ]);

    $channelB = Channel::factory()->create([
        'default' => false,
    ]);

    $user = User::factory()->create();

    $userCartOnChannelA = Cart::factory()->create([
        'user_id' => $user->id,
        'channel_id' => $channelA->id,
        'currency_id' => $currency->id,
    ]);

    $guestCartOnChannelB = Cart::factory()->create([
        'channel_id' => $channelB->id,
        'currency_id' => $currency->id,
    ]);

    CartSession::setChannel($channelB);

    $action = new AssociateUser;
    $action->execute($guestCartOnChannelB, $user, 'merge');

    // The channel A cart belongs to a different channel to the one being
    // logged into, so it must not have been merged into the guest cart.
    $this->assertDatabaseHas((new Cart)->getTable(), [
        'id' => $userCartOnChannelA->id,
        'merged_id' => null,
    ]);

    $this->assertDatabaseHas((new Cart)->getTable(), [
        'id' => $guestCartOnChannelB->id,
        'user_id' => $user->id,
    ]);
});

test('does not override with a user cart from a different channel', function () {
    $currency = Currency::factory()->create([
        'default' => true,
    ]);

    $channelA = Channel::factory()->create([
        'default' => true,
    ]);

    $channelB = Channel::factory()->create([
        'default' => false,
    ]);

    $user = User::factory()->create();

    $userCartOnChannelA = Cart::factory()->create([
        'user_id' => $user->id,
        'channel_id' => $channelA->id,
        'currency_id' => $currency->id,
    ]);

    $guestCartOnChannelB = Cart::factory()->create([
        'channel_id' => $channelB->id,
        'currency_id' => $currency->id,
    ]);

    CartSession::setChannel($channelB);

    $action = new AssociateUser;
    $action->execute($guestCartOnChannelB, $user, 'override');

    // The channel A cart is out of scope for the override policy too, so
    // it must not be marked as merged/overridden.
    $this->assertDatabaseHas((new Cart)->getTable(), [
        'id' => $userCartOnChannelA->id,
        'merged_id' => null,
    ]);
});
