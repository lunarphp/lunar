<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Currencies\UpdateCurrency;
use Lunar\Core\Exceptions\CurrencyActionException;
use Lunar\Core\Models\Currency;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('updates the currency attributes', function () {
    $currency = Currency::factory()->create(['name' => 'Old Name', 'default' => false]);

    app(UpdateCurrency::class)->execute($currency, ['name' => 'New Name']);

    $this->assertDatabaseHas('lunar_currencies', [
        'id' => $currency->id,
        'name' => 'New Name',
    ]);
});

test('promoting to default demotes the previous default and forces enabled', function () {
    $previous = Currency::factory()->create(['default' => true]);
    $currency = Currency::factory()->create(['default' => false, 'enabled' => false]);

    app(UpdateCurrency::class)->execute($currency, ['default' => true]);

    expect($previous->refresh()->default)->toBeFalse()
        ->and($currency->refresh()->default)->toBeTrue()
        ->and($currency->enabled)->toBeTrue();
});

test('refuses to unset the default flag directly', function () {
    $currency = Currency::factory()->create(['default' => true]);

    expect(fn () => app(UpdateCurrency::class)->execute($currency, ['default' => false]))
        ->toThrow(CurrencyActionException::class);
});

test('refuses to disable the default currency', function () {
    $currency = Currency::factory()->create(['default' => true, 'enabled' => true]);

    expect(fn () => app(UpdateCurrency::class)->execute($currency, ['enabled' => false]))
        ->toThrow(CurrencyActionException::class);
});
