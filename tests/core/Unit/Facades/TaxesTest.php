<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Contracts\TaxManager;
use Lunar\Core\Facades\Taxes;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\ValueObjects\Cart\TaxBreakdown;
use Lunar\Tests\Core\Stubs\TestTaxDriver;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('accessor is correct', function () {
    expect(Taxes::getFacadeAccessor())->toEqual(TaxManager::class);
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
