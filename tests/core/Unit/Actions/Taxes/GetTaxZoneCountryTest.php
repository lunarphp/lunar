<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Actions\Taxes\GetTaxZoneCountry;
use Lunar\Models\Country;
use Lunar\Models\TaxZoneCountry;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class)->group('taxes');

test('can match country id', function () {
    $belgium = Country::factory()->create([
        'name' => 'Belgium',
    ]);

    $uk = Country::factory()->create([
        'name' => 'United Kingdom',
    ]);

    $taxZoneBelgium = TaxZoneCountry::factory()->create([
        'country_id' => $belgium->id,
    ]);

    $taxZoneUk = TaxZoneCountry::factory()->create([
        'country_id' => $uk->id,
    ]);

    $zone = app(GetTaxZoneCountry::class)->execute($uk->id);

    expect($zone->id)->toEqual($taxZoneUk->id);
});

test('can mismatch country id', function () {
    $belgium = Country::factory()->create([
        'name' => 'Belgium',
    ]);

    $uk = Country::factory()->create([
        'name' => 'United Kingdom',
    ]);

    $taxZoneBelgium = TaxZoneCountry::factory()->create([
        'country_id' => $belgium->id,
    ]);

    $zone = app(GetTaxZoneCountry::class)->execute($uk->id);

    expect($zone)->toBeNull();
});
