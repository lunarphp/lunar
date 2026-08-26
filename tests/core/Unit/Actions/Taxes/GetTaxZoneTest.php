<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Actions\Taxes\GetTaxZone;
use Lunar\Models\Address;
use Lunar\Models\Country;
use Lunar\Models\State;
use Lunar\Models\TaxZone;
use Lunar\Models\TaxZoneCountry;
use Lunar\Models\TaxZonePostcode;
use Lunar\Models\TaxZoneState;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class)
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

test('can ignore a state tax zone from another country', function () {
    $australia = Country::factory()->create([
        'name' => 'Australia',
    ]);

    $unitedStates = Country::factory()->create([
        'name' => 'United States',
    ]);

    $washington = State::factory()->create([
        'country_id' => $unitedStates->id,
        'code' => 'WA',
        'name' => 'Washington',
    ]);

    TaxZoneState::factory()->create([
        'tax_zone_id' => TaxZone::factory(['default' => false]),
        'state_id' => $washington->id,
    ]);

    $auZone = TaxZoneCountry::factory()->create([
        'tax_zone_id' => TaxZone::factory(['default' => false]),
        'country_id' => $australia->id,
    ]);

    TaxZone::factory(['default' => true])->create();

    // Western Australia is also "WA", but has no tax zone of its own, so the
    // Australian country zone should apply.
    $address = Address::factory()->create([
        'postcode' => '6000',
        'state' => 'WA',
        'country_id' => $australia->id,
    ]);

    $zone = app(GetTaxZone::class)->execute($address);

    expect($zone->id)->toEqual($auZone->tax_zone_id);
});
