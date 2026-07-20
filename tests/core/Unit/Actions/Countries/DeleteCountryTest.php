<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Countries\DeleteCountry;
use Lunar\Core\Exceptions\CountryActionException;
use Lunar\Core\Models\Address;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\State;
use Lunar\Core\Models\TaxZoneCountry;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('deletes an unreferenced country', function () {
    $country = Country::factory()->create();

    app(DeleteCountry::class)->execute($country);

    $this->assertDatabaseMissing('lunar_countries', ['id' => $country->id]);
});

test('refuses to delete a country with states', function () {
    $country = Country::factory()->create();
    State::factory()->create(['country_id' => $country->id]);

    expect(fn () => app(DeleteCountry::class)->execute($country))
        ->toThrow(CountryActionException::class);
});

test('refuses to delete a country referenced by addresses', function () {
    $country = Country::factory()->create();
    Address::factory()->create(['country_id' => $country->id]);

    expect(fn () => app(DeleteCountry::class)->execute($country))
        ->toThrow(CountryActionException::class);
});

test('refuses to delete a country referenced by a tax zone', function () {
    $country = Country::factory()->create();
    TaxZoneCountry::factory()->create(['country_id' => $country->id]);

    expect(fn () => app(DeleteCountry::class)->execute($country))
        ->toThrow(CountryActionException::class);
});
