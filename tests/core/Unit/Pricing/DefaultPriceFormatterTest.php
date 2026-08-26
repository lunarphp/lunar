<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\Currency;
use Lunar\Core\Pricing\DefaultPriceFormatter;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('clamps a unit quantity below one to one', function () {
    $currency = Currency::factory()->create([
        'code' => 'GBP',
        'decimal_places' => 2,
    ]);

    // unitDecimal() divides by the unit quantity, so zero would throw and a
    // negative would invert the price.
    $formatter = new DefaultPriceFormatter(1500, $currency, 0);

    expect($formatter->unitQty)->toBe(1)
        ->and($formatter->unitDecimal())->toEqual(15.00)
        ->and($formatter->unitFormatted())->toEqual('£15.00');
});

test('divides by a unit quantity of more than one', function () {
    $currency = Currency::factory()->create([
        'code' => 'GBP',
        'decimal_places' => 2,
    ]);

    $formatter = new DefaultPriceFormatter(1500, $currency, 3);

    expect($formatter->unitDecimal())->toEqual(5.00);
});
