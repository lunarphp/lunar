<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Lunar\Actions\Taxes\GetTaxZone;
use Lunar\Models\Address;
use Lunar\Models\Country;
use Lunar\Models\State;
use Lunar\Models\TaxZone;
use Lunar\Models\TaxZoneCountry;
use Lunar\Models\TaxZonePostcode;
use Lunar\Models\TaxZoneState;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->group('taxes');

test('can prioritize taxzones', function () {
    $postcode = 'SW1A 0AA';

    $state = State::factory()->create([
        'code' => 'AL',
        'name' => 'Alabama',
    ]);

    $country = Country::factory()->create([
        'name' => 'Belgium',
    ]);

    $taxZonePostcode = TaxZonePostcode::factory()->create([
        'tax_zone_id' => TaxZone::factory(['default' => false]),
        'postcode' => $postcode,
    ]);

    $taxZoneState = TaxZoneState::factory()->create([
        'tax_zone_id' => TaxZone::factory(['default' => false]),
        'state_id' => $state->id,
    ]);

    $taxZoneCountry = TaxZoneCountry::factory()->create([
        'tax_zone_id' => TaxZone::factory(['default' => false]),
        'country_id' => $country->id,
    ]);

    $defaultTaxZone = TaxZone::factory(['default' => true])->create();

    // postcode, state and country match => postcode tax zone should be returned
    $addressWithAllMatching = Address::factory()->create([
        'postcode' => $postcode,
        'state' => $state->name,
        'country_id' => $country->id,
    ]);

    $zone1 = app(GetTaxZone::class)->execute($addressWithAllMatching);

    expect($zone1->id)->toEqual($taxZonePostcode->tax_zone_id);

    // only state and country match => state tax zone should be returned
    $addressWithOnlyStateAndCountryMatching = Address::factory()->create([
        'postcode' => '1234AB',
        'state' => $state->name,
        'country_id' => $country->id,
    ]);

    $zone2 = app(GetTaxZone::class)->execute($addressWithOnlyStateAndCountryMatching);

    expect($zone2->id)->toEqual($taxZoneState->tax_zone_id);

    // only country matches => country tax zone should be returned
    $addressWithOnlyCountryMatching = Address::factory()->create([
        'postcode' => '1234AB',
        'state' => 'Alaska',
        'country_id' => $country->id,
    ]);

    $zone3 = app(GetTaxZone::class)->execute($addressWithOnlyCountryMatching);

    expect($zone3->id)->toEqual($taxZoneCountry->tax_zone_id);

    // nothing matches => default tax zone should be returned
    $addressWithOnlyCountryMatching = Address::factory()->create([
        'postcode' => '1234AB',
        'state' => 'Alaska',
        'country_id' => 123,
    ]);

    $zone3 = app(GetTaxZone::class)->execute($addressWithOnlyCountryMatching);

    expect($zone3->id)->toEqual($defaultTaxZone->id);
});
