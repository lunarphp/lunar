<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Actions\Carts\AssociateUser;
use Lunar\Models\Cart;
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
