<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Casts\TaxBreakdown as TaxBreakdownCasts;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Order;
use Lunar\Core\ValueObjects\Cart\TaxBreakdown;
use Lunar\Core\ValueObjects\Cart\TaxBreakdownAmount;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('can set from value object', function () {
    $currency = Currency::factory()->create();
    $order = Order::factory()->create();

    $taxBreakdownValueObject = new TaxBreakdown;

    $taxBreakdownValueObject->addAmount(
        new TaxBreakdownAmount(
            price: new PriceValue(100, $currency),
            identifier: 'TAX_AMOUNT_1',
            description: 'Test Tax Breakdown Amount',
            percentage: 20
        )
    );

    $breakDown = new TaxBreakdownCasts;

    $result = $breakDown->set($order, 'tax_breakdown', $taxBreakdownValueObject, []);

    expect($result)->toHaveKey('tax_breakdown');
    expect($result['tax_breakdown'])->toBeJson();
});

test('can cast to and from model', function () {
    $currency = Currency::factory()->create();
    $order = Order::factory()->create();

    $taxBreakdownValueObject = new TaxBreakdown;

    $taxBreakdownValueObject->addAmount(
        new TaxBreakdownAmount(
            price: new PriceValue(100, $currency),
            identifier: 'TAX_AMOUNT_1',
            description: 'Test Tax Breakdown Amount',
            percentage: 20
        )
    );

    $order->update([
        'tax_breakdown' => $taxBreakdownValueObject,
    ]);

    $breakdown = $order->refresh()->tax_breakdown;
    expect($breakdown)->toBeInstanceOf(TaxBreakdown::class);
});
