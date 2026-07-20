<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Currencies\DeleteCurrency;
use Lunar\Core\Exceptions\CurrencyActionException;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('deletes a currency without prices', function () {
    $currency = Currency::factory()->create(['default' => false]);

    app(DeleteCurrency::class)->execute($currency);

    $this->assertDatabaseMissing('lunar_currencies', ['id' => $currency->id]);
});

test('refuses to delete the default currency', function () {
    $currency = Currency::factory()->create(['default' => true]);

    expect(fn () => app(DeleteCurrency::class)->execute($currency))
        ->toThrow(CurrencyActionException::class);
});

test('refuses to delete a currency with prices', function () {
    $currency = Currency::factory()->create(['default' => false]);
    $variant = ProductVariant::factory()->create();
    Price::factory()->create([
        'currency_id' => $currency->id,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
    ]);

    expect(fn () => app(DeleteCurrency::class)->execute($currency))
        ->toThrow(CurrencyActionException::class);

    $this->assertDatabaseHas('lunar_currencies', ['id' => $currency->id]);
});
