<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Carts\AddAddress;
use Lunar\Core\Models\Address;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartAddress;
use Lunar\Core\Models\Currency;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('can add address from addressable', function () {
    $address = Address::factory()->create();

    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $action = new AddAddress;

    $this->assertDatabaseMissing((new CartAddress)->getTable(), [
        'cart_id' => $cart->id,
    ]);

    $action->execute($cart, $address, 'billing');

    $attributes = $address->getAttributes();
    unset($attributes['shipping_default']);
    unset($attributes['billing_default']);
    unset($attributes['public_id']); // a copied address is a new record with its own external id

    $this->assertDatabaseHas((new CartAddress)->getTable(), array_merge([
        'cart_id' => $cart->id,
        'type' => 'billing',
    ], $attributes));
});

test('can add address from array', function () {
    $address = Address::factory()->create();

    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $action = new AddAddress;

    $this->assertDatabaseMissing((new CartAddress)->getTable(), [
        'cart_id' => $cart->id,
    ]);

    $action->execute($cart, $address->toArray(), 'billing');

    $attributes = $address->getAttributes();
    unset($attributes['shipping_default']);
    unset($attributes['billing_default']);
    unset($attributes['public_id']); // a copied address is a new record with its own external id

    $this->assertDatabaseHas((new CartAddress)->getTable(), array_merge([
        'cart_id' => $cart->id,
        'type' => 'billing',
    ], $attributes));
});

test('can override existing address', function () {
    $addressA = Address::factory()->create([
        'postcode' => 'CBA 31',
    ]);

    $addressB = Address::factory()->create([
        'postcode' => 'ABC 123',
    ]);

    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $action = new AddAddress;

    $this->assertDatabaseMissing((new CartAddress)->getTable(), [
        'cart_id' => $cart->id,
    ]);

    $action->execute($cart, $addressA, 'billing');

    $attributes = $addressA->getAttributes();
    unset($attributes['shipping_default']);
    unset($attributes['billing_default']);
    unset($attributes['public_id']); // a copied address is a new record with its own external id

    $this->assertDatabaseHas((new CartAddress)->getTable(), array_merge([
        'cart_id' => $cart->id,
        'type' => 'billing',
    ], $attributes));

    $action->execute($cart, $addressB, 'billing');

    $attributes = $addressA->getAttributes();
    unset($attributes['shipping_default']);
    unset($attributes['billing_default']);
    unset($attributes['public_id']); // a copied address is a new record with its own external id

    $this->assertDatabaseMissing((new CartAddress)->getTable(), array_merge([
        'cart_id' => $cart->id,
        'type' => 'billing',
    ], $attributes));

    $attributes = $addressB->getAttributes();
    unset($attributes['shipping_default']);
    unset($attributes['billing_default']);
    unset($attributes['public_id']); // a copied address is a new record with its own external id

    $this->assertDatabaseHas((new CartAddress)->getTable(), array_merge([
        'cart_id' => $cart->id,
        'type' => 'billing',
    ], $attributes));
});
