<?php

uses(\Lunar\Tests\Core\TestCase::class);
use Lunar\Models\TaxRate;
use Lunar\Models\TaxRateAmount;
use Lunar\Models\TaxZone;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can make a tax rate', function () {
    $data = [
        'name' => 'VAT',
        'tax_zone_id' => TaxZone::factory()->create()->id,
    ];

    $rate = TaxRate::factory()->create($data);

    $this->assertDatabaseHas((new TaxRate)->getTable(), $data);

    expect($rate->taxZone)->toBeInstanceOf(TaxZone::class);
});

test('tax rate can have amounts', function () {
    $data = [
        'name' => 'VAT',
        'tax_zone_id' => TaxZone::factory()->create()->id,
    ];

    $rate = TaxRate::factory()->create($data);

    $this->assertDatabaseHas((new TaxRate)->getTable(), $data);

    expect($rate->taxRateAmounts)->toHaveCount(0);

    $rate->taxRateAmounts()->create(TaxRateAmount::factory()->make()->toArray());

    expect($rate->refresh()->taxRateAmounts)->toHaveCount(1);
});
