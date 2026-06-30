<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Currency;
use Lunar\Core\Modifiers\ShippingModifier;
use Lunar\Core\Modifiers\ShippingModifiers;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

beforeEach(function () {
    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);

    $this->cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $this->class = new class extends ShippingModifier
    {
        public function handle(Cart $cart, Closure $next)
        {
            return $next($cart);
        }
    };

    $this->shippingModifiers = new ShippingModifiers;
});

test('can add modifier', function () {
    $this->shippingModifiers->add($this->class::class);

    expect($this->shippingModifiers->getModifiers())->toHaveCount(1);
});

test('can remove modifier', function () {
    $this->shippingModifiers->remove($this->class::class);

    expect($this->shippingModifiers->getModifiers())->toHaveCount(0);
});
