<?php

uses(\Lunar\Tests\TestCase::class);

use Lunar\Base\TaxManagerInterface;
use Lunar\Base\ValueObjects\Cart\TaxBreakdown;
use Lunar\Facades\Taxes;
use Lunar\Models\Currency;
use Lunar\Models\ProductVariant;
use Stubs\TestTaxDriver;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('accessor is correct', function () {
    expect(Taxes::getFacadeAccessor())->toEqual(TaxManagerInterface::class);
});

test('can extend taxes', function () {
    Taxes::extend('testing', function ($app) {
        return $app->make(TestTaxDriver::class);
    });

    expect(Taxes::driver('testing'))->toBeInstanceOf(TestTaxDriver::class);

    $result = Taxes::driver('testing')->setPurchasable(
        ProductVariant::factory()->create()
    )->setCurrency(
        Currency::factory()->create()
    )->getBreakdown(123);

    expect($result)->toBeInstanceOf(TaxBreakdown::class);
});
