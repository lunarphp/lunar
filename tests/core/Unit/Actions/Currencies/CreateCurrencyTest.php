<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Currencies\CreateCurrency;
use Lunar\Core\Models\Currency;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('creates a currency with the given attributes', function () {
    $currency = app(CreateCurrency::class)->execute([
        'code' => 'GBP',
        'name' => 'Pound Sterling',
        'exchange_rate' => 1,
        'decimal_places' => 2,
        'enabled' => true,
    ]);

    expect($currency)->toBeInstanceOf(Currency::class);

    $this->assertDatabaseHas('lunar_currencies', [
        'id' => $currency->id,
        'code' => 'GBP',
    ]);
});

test('demotes the previous default when created as default', function () {
    $previous = Currency::factory()->create(['default' => true]);

    app(CreateCurrency::class)->execute([
        'code' => 'GBP',
        'name' => 'Pound Sterling',
        'exchange_rate' => 1,
        'decimal_places' => 2,
        'default' => true,
        'enabled' => false,
    ]);

    expect($previous->refresh()->default)->toBeFalse();
    // The default currency is always enabled, whatever was submitted.
    expect(Currency::query()->where('default', true)->sole()->enabled)->toBeTrue();
});
