<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Base\ValueObjects\Cart\ShippingBreakdown;
use Lunar\Base\ValueObjects\Cart\ShippingBreakdownItem;
use Lunar\DataTypes\Price as PriceDataType;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Pipelines\Cart\CalculateShippingSubTotal;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('sums the shipping breakdown into the cart sub total', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $cart->shippingBreakdown = new ShippingBreakdown(collect([
        'first' => new ShippingBreakdownItem(
            name: 'First',
            identifier: 'first',
            price: new PriceDataType(500, $currency, 1),
        ),
        'second' => new ShippingBreakdownItem(
            name: 'Second',
            identifier: 'second',
            price: new PriceDataType(250, $currency, 1),
        ),
    ]));

    app(CalculateShippingSubTotal::class)->handle($cart, fn ($cart) => $cart);

    expect($cart->shippingSubTotal)->toBeInstanceOf(PriceDataType::class);
    expect($cart->shippingSubTotal->value)->toEqual(750);
});

test('falls back to zero when no breakdown is set', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    app(CalculateShippingSubTotal::class)->handle($cart, fn ($cart) => $cart);

    expect($cart->shippingSubTotal)->toBeInstanceOf(PriceDataType::class);
    expect($cart->shippingSubTotal->value)->toEqual(0);
});

test('reflects breakdown mutations made between ApplyShipping and CalculateShippingSubTotal', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $cart->shippingBreakdown = new ShippingBreakdown(collect([
        'base' => new ShippingBreakdownItem(
            name: 'Base',
            identifier: 'base',
            price: new PriceDataType(500, $currency, 1),
        ),
    ]));

    $cart->shippingBreakdown->items->put(
        'surcharge',
        new ShippingBreakdownItem(
            name: 'Fuel surcharge',
            identifier: 'surcharge',
            price: new PriceDataType(125, $currency, 1),
        ),
    );

    app(CalculateShippingSubTotal::class)->handle($cart, fn ($cart) => $cart);

    expect($cart->shippingSubTotal->value)->toEqual(625);
});
