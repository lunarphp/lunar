<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Actions\Taxes\GetTaxZoneState;
use Lunar\Models\Country;
use Lunar\Models\State;
use Lunar\Models\TaxZoneState;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('can match exact state name', function () {
    $california = State::factory()->create([
        'code' => 'CA',
        'name' => 'California',
    ]);

    $alabama = State::factory()->create([
        'code' => 'AL',
        'name' => 'Alabama',
    ]);

    TaxZoneState::factory()->create([
        'state_id' => $california->id,
    ]);

    $al = TaxZoneState::factory()->create([
        'state_id' => $alabama->id,
    ]);

    $zone = app(GetTaxZoneState::class)->execute('Alabama');

    expect($zone->id)->toEqual($al->id);
});

test('can match exact state code', function () {
    $california = State::factory()->create([
        'code' => 'CA',
        'name' => 'California',
    ]);

    $alabama = State::factory()->create([
        'code' => 'AL',
        'name' => 'Alabama',
    ]);

    TaxZoneState::factory()->create([
        'state_id' => $california->id,
    ]);

    $al = TaxZoneState::factory()->create([
        'state_id' => $alabama->id,
    ]);

    $zone = app(GetTaxZoneState::class)->execute('AL');

    expect($zone)->not->toBeNull();

    expect($zone?->id)->toEqual($al->id);
});

test('can mismatch exact state name', function () {
    $california = State::factory()->create([
        'code' => 'CA',
        'name' => 'California',
    ]);

    $alabama = State::factory()->create([
        'code' => 'AL',
        'name' => 'Alabama',
    ]);

    TaxZoneState::factory()->create([
        'state_id' => $california->id,
    ]);

    $al = TaxZoneState::factory()->create([
        'state_id' => $alabama->id,
    ]);

    $zone = app(GetTaxZoneState::class)->execute('Alaba');

    expect($zone)->toBeNull();

    $this->assertNotEquals($al->id, $zone?->id);
});

test('can match a state in the given country', function () {
    $australia = Country::factory()->create([
        'name' => 'Australia',
    ]);

    $unitedStates = Country::factory()->create([
        'name' => 'United States',
    ]);

    $westernAustralia = State::factory()->create([
        'country_id' => $australia->id,
        'code' => 'WA',
        'name' => 'Western Australia',
    ]);

    $washington = State::factory()->create([
        'country_id' => $unitedStates->id,
        'code' => 'WA',
        'name' => 'Washington',
    ]);

    // Created first, so an unscoped lookup returns this one.
    TaxZoneState::factory()->create([
        'state_id' => $washington->id,
    ]);

    $auZone = TaxZoneState::factory()->create([
        'state_id' => $westernAustralia->id,
    ]);

    $zone = app(GetTaxZoneState::class)->execute('WA', $australia->id);

    expect($zone?->id)->toEqual($auZone->id);
});

test('can mismatch a state in another country', function () {
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
        'state_id' => $washington->id,
    ]);

    $zone = app(GetTaxZoneState::class)->execute('WA', $australia->id);

    expect($zone)->toBeNull();
});

test('can match a state which is not assigned to a country', function () {
    $australia = Country::factory()->create([
        'name' => 'Australia',
    ]);

    $alabama = State::factory()->create([
        'country_id' => null,
        'code' => 'AL',
        'name' => 'Alabama',
    ]);

    $alZone = TaxZoneState::factory()->create([
        'state_id' => $alabama->id,
    ]);

    $zone = app(GetTaxZoneState::class)->execute('AL', $australia->id);

    expect($zone?->id)->toEqual($alZone->id);
});
