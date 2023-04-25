<?php

namespace Lunar\Tests\Unit\Actions\Carts;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Actions\Carts\AddAddress;
use Lunar\Models\Address;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\Currency;
use Lunar\Tests\TestCase;
use function Livewire\invade;

/**
 * @group lunar.actions
 * @group lunar.actions.carts.now
 */
class AddAddressTest extends TestCase
{
    use RefreshDatabase;

    protected AddAddress $action;

    protected array $fillableAttributes;

    protected string $cartAddressTable;

    public function setUp(): void
    {
        parent::setUp();

        $this->action = new AddAddress;

        $this->fillableAttributes = invade($this->action)->__get('fillableAttributes');

        $this->cartAddressTable = (new CartAddress)->getTable();
    }

    protected function getCartAddressAttributesFromAddress(Address $address): array
    {
        return collect($address->getAttributes())->only($this->fillableAttributes)->toArray();
    }
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

        $this->assertDatabaseMissing($this->cartAddressTable, [
            'cart_id' => $cart->id,
        ]);

        $this->action->execute($cart, $address, 'billing');

        $attributes = $this->getCartAddressAttributesFromAddress($address);

        $this->assertDatabaseHas($this->cartAddressTable, array_merge([
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

        $this->assertDatabaseMissing($this->cartAddressTable, [
            'cart_id' => $cart->id,
        ]);

        $this->action->execute($cart, $address->toArray(), 'billing');

        $attributes = $this->getCartAddressAttributesFromAddress($address);

        $this->assertDatabaseHas($this->cartAddressTable, array_merge([
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


        $this->assertDatabaseMissing($this->cartAddressTable, [
            'cart_id' => $cart->id,
        ]);

        $this->action->execute($cart, $addressA, 'billing');

        $attributesAddressA = $this->getCartAddressAttributesFromAddress($addressA);

        $this->assertDatabaseHas($this->cartAddressTable, array_merge([
            'cart_id' => $cart->id,
            'type' => 'billing',
        ], $attributesAddressA));

        $this->action->execute($cart, $addressB, 'billing');

        $this->assertDatabaseMissing($this->cartAddressTable, array_merge([
            'cart_id' => $cart->id,
            'type' => 'billing',
        ], $attributesAddressA));

        $attributesAddressB = $this->getCartAddressAttributesFromAddress($addressB);

        $this->assertDatabaseHas($this->cartAddressTable, array_merge([
            'cart_id' => $cart->id,
            'type' => 'billing',
        ], $attributesAddressB));
    }
}
