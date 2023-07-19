<?php

namespace Lunar\Tests\Unit\Actions\Carts;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Actions\Carts\AddAddress;
use Lunar\Models\Address;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\Currency;
use Lunar\Tests\TestCase;

/**
 * @group lunar.actions
 * @group lunar.actions.carts.now
 */
class AddAddressTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function can_add_address_from_addressable()
    {
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
        unset($attributes['id']);
        unset($attributes['shipping_default']);
        unset($attributes['billing_default']);
        unset($attributes['meta']);

        $this->assertDatabaseHas((new CartAddress)->getTable(), array_merge([
            'cart_id' => $cart->id,
            'type' => 'billing',
        ], $attributes));
    }

    /**
     * @test
     */
    public function can_add_address_from_array()
    {
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
        unset($attributes['id']);
        unset($attributes['shipping_default']);
        unset($attributes['billing_default']);
        unset($attributes['meta']);

        $this->assertDatabaseHas((new CartAddress)->getTable(), array_merge([
            'cart_id' => $cart->id,
            'type' => 'billing',
        ], $attributes));
    }

    /**
     * @test
     */
    public function can_override_existing_address()
    {
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
        unset($attributes['id']);
        unset($attributes['shipping_default']);
        unset($attributes['billing_default']);
        unset($attributes['meta']);

        $this->assertDatabaseHas((new CartAddress)->getTable(), array_merge([
            'cart_id' => $cart->id,
            'type' => 'billing',
        ], $attributes));

        $action->execute($cart, $addressB, 'billing');

        $attributes = $addressA->getAttributes();
        unset($attributes['id']);
        unset($attributes['shipping_default']);
        unset($attributes['billing_default']);
        unset($attributes['meta']);

        $this->assertDatabaseMissing((new CartAddress)->getTable(), array_merge([
            'cart_id' => $cart->id,
            'type' => 'billing',
        ], $attributes));

        $attributes = $addressB->getAttributes();
        unset($attributes['id']);
        unset($attributes['shipping_default']);
        unset($attributes['billing_default']);
        unset($attributes['meta']);

        $this->assertDatabaseHas((new CartAddress)->getTable(), array_merge([
            'cart_id' => $cart->id,
            'type' => 'billing',
        ], $attributes));
    }
}
