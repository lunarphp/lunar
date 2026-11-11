<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\Currency;
use Lunar\Shipping\Panel\Support\MethodData;
use Lunar\Tests\Shipping\PanelTestCase;

uses(PanelTestCase::class, RefreshDatabase::class)->group('shipping', 'shipping-panel');

beforeEach(function () {
    Currency::factory()->create(['code' => 'GBP', 'decimal_places' => 2, 'default' => true]);
    Currency::factory()->create(['code' => 'USD', 'decimal_places' => 2, 'default' => false]);
    $this->data = app(MethodData::class);
});

test('a single minimum spend applies to every currency, as the driver reads it', function () {
    $form = $this->data->toForm(['minimum_spend' => 5000]);

    expect($form['minimum_spend'])->toBe(['GBP' => 50.0, 'USD' => 50.0])
        ->and($this->data->toStorage($form)['minimum_spend'])->toBe(['GBP' => 5000, 'USD' => 5000]);
});

test('a minimum spend in a disabled currency survives an edit', function () {
    Currency::factory()->create(['code' => 'EUR', 'decimal_places' => 2, 'default' => false, 'enabled' => false]);

    $form = $this->data->toForm(['minimum_spend' => ['GBP' => 5000, 'EUR' => 6000]]);

    expect($this->data->toStorage($form)['minimum_spend'])->toBe(['GBP' => 5000, 'EUR' => 6000]);
});
