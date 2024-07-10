<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Lunar\Models\Country;
use Lunar\Models\CustomerGroup;
use Lunar\Models\State;
use Lunar\Models\TaxZone;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can make a tax zone class', function () {
    $data = [
        'name' => 'UK Mainland',
        'zone_type' => 'state',
        'price_display' => 'tax_inclusive',
        'active' => true,
        'default' => true,
    ];

    TaxZone::factory()->create($data);

    $this->assertDatabaseHas((new TaxZone())->getTable(), $data);
});

test('tax zone can have countries', function () {
    $data = [
        'name' => 'UK Mainland',
        'zone_type' => 'state',
        'price_display' => 'tax_inclusive',
        'active' => true,
        'default' => true,
    ];

    $zone = TaxZone::factory()->create($data);

    $this->assertDatabaseHas((new TaxZone())->getTable(), $data);

    $country = Country::factory()->create();

    expect($zone->refresh()->countries)->toHaveCount(0);

    $zone->countries()->create([
        'country_id' => $country->id,
    ]);

    expect($zone->refresh()->countries)->toHaveCount(1);
});

test('tax zone can have states', function () {
    $data = [
        'name' => 'L.A.',
        'zone_type' => 'state',
        'price_display' => 'tax_inclusive',
        'active' => true,
        'default' => true,
    ];

    $zone = TaxZone::factory()->create($data);

    $this->assertDatabaseHas((new TaxZone())->getTable(), $data);

    $country = Country::factory()->create();
    $state = State::factory()->create([
        'country_id' => $country->id,
    ]);

    expect($zone->refresh()->states)->toHaveCount(0);

    $zone->states()->create([
        'state_id' => $state->id,
    ]);

    expect($zone->refresh()->states)->toHaveCount(1);
});

test('tax zone can have postcodes', function () {
    $data = [
        'name' => 'L.A.',
        'zone_type' => 'state',
        'price_display' => 'tax_inclusive',
        'active' => true,
        'default' => true,
    ];

    $zone = TaxZone::factory()->create($data);

    $this->assertDatabaseHas((new TaxZone())->getTable(), $data);

    $country = Country::factory()->create();

    expect($zone->refresh()->postcodes)->toHaveCount(0);

    $zone->postcodes()->create([
        'country_id' => $country->id,
        'postcode' => 12345,
    ]);

    expect($zone->refresh()->postcodes)->toHaveCount(1);
});

test('tax zone can have customer groups', function () {
    $data = [
        'name' => 'L.A.',
        'zone_type' => 'state',
        'price_display' => 'tax_inclusive',
        'active' => true,
        'default' => true,
    ];

    $zone = TaxZone::factory()->create($data);

    $this->assertDatabaseHas((new TaxZone())->getTable(), $data);

    $country = Country::factory()->create();

    expect($zone->refresh()->customerGroups)->toHaveCount(0);

    $zone->customerGroups()->create([
        'customer_group_id' => CustomerGroup::factory()->create()->id,
    ]);

    expect($zone->refresh()->customerGroups)->toHaveCount(1);
});

test('can delete a tax zone', function () {
    $data = [
        'name' => 'L.A.',
        'zone_type' => 'state',
        'price_display' => 'tax_inclusive',
        'active' => true,
        'default' => true,
    ];

    $zone = TaxZone::factory()->create($data);

    \Pest\Laravel\assertDatabaseHas((new TaxZone())->getTable(), $data);

    $country = Country::factory()->create();
    $state = State::factory()->create();

    expect($zone->refresh()->customerGroups)->toHaveCount(0);

    $zone->customerGroups()->create([
        'customer_group_id' => CustomerGroup::factory()->create()->id,
    ]);

    expect($zone->refresh()->customerGroups)->toHaveCount(1);

    $zone->countries()->create(['country_id' => $country->id]);
    $zone->states()->create(['state_id' => $state->id]);
    $zone->postcodes()->create([
        'country_id' => $country->id,
        'postcode' => 'ABC 123',
    ]);

    \Lunar\Models\TaxRate::factory()->create([
        'tax_zone_id' => $zone->id,
    ]);

    assertDatabaseHas(\Lunar\Models\TaxZoneCountry::class, ['tax_zone_id' => $zone->id]);
    assertDatabaseHas(\Lunar\Models\TaxZoneCustomerGroup::class, ['tax_zone_id' => $zone->id]);
    assertDatabaseHas(\Lunar\Models\TaxZoneState::class, ['tax_zone_id' => $zone->id]);
    assertDatabaseHas(\Lunar\Models\TaxZonePostcode::class, ['tax_zone_id' => $zone->id]);
    assertDatabaseHas(\Lunar\Models\TaxRate::class, ['tax_zone_id' => $zone->id]);

    $zone->delete();

    assertDatabaseMissing(\Lunar\Models\TaxZoneCountry::class, ['tax_zone_id' => $zone->id]);
    assertDatabaseMissing(\Lunar\Models\TaxZoneCustomerGroup::class, ['tax_zone_id' => $zone->id]);
    assertDatabaseMissing(\Lunar\Models\TaxZoneState::class, ['tax_zone_id' => $zone->id]);
    assertDatabaseMissing(\Lunar\Models\TaxZonePostcode::class, ['tax_zone_id' => $zone->id]);
    assertDatabaseMissing(\Lunar\Models\TaxRate::class, ['tax_zone_id' => $zone->id]);
})->group('foo');
