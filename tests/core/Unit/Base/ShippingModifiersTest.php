<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Lunar\Base\ShippingModifiers;
use \Lunar\Base\ShippingModifier;
use Lunar\Models\Cart;
use Lunar\Models\Currency;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);

    $this->cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $this->class = new class extends ShippingModifier
    {
        function handle(Cart $cart)
        {
            //
        }
    };

    $this->shippingModifiers = new ShippingModifiers();
});

test('can add modifier', function () {
    $this->shippingModifiers->add($this->class::class);

    expect($this->shippingModifiers->getModifiers())->toHaveCount(1);
});

function can_remove_modifier()
{
    $this->shippingModifiers->remove($this->class::class);

    expect($this->shippingModifiers->getModifiers())->toHaveCount(0);
}