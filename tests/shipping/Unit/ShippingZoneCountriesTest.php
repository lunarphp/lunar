<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\State;
use Lunar\Shipping\Checkout\ShippingZoneCountries;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingRate;
use Lunar\Shipping\Models\ShippingZone;
use Lunar\Tests\Shipping\TestCase;
use Lunar\Tests\Shipping\TestUtils;

uses(TestCase::class)->group('shipping', 'shipping-countries');
uses(RefreshDatabase::class);
uses(TestUtils::class);

/**
 * A zone only counts when it carries a rate for an enabled method: a zone
 * with no rates, or whose only method is switched off, ships nowhere.
 */
function zoneWithRate(string $type, bool $enabled = true): ShippingZone
{
    $zone = ShippingZone::factory()->create(['type' => $type]);
    $method = ShippingMethod::factory()->create(['enabled' => $enabled]);
    ShippingRate::factory()->create([
        'shipping_method_id' => $method->id,
        'shipping_zone_id' => $zone->id,
    ]);

    return $zone;
}

beforeEach(function () {
    Currency::factory()->create(['default' => true]);
});

test('lists the countries of zones that carry an enabled rate, sorted by name', function () {
    $uk = Country::factory()->create(['iso2' => 'GB', 'name' => 'United Kingdom']);
    $ireland = Country::factory()->create(['iso2' => 'IE', 'name' => 'Ireland']);
    Country::factory()->create(['iso2' => 'FR', 'name' => 'France']);

    zoneWithRate('countries')->countries()->attach($uk);
    zoneWithRate('postcodes')->countries()->attach($uk);
    zoneWithRate('countries')->countries()->attach($ireland);

    $codes = (new ShippingZoneCountries)->available(Cart::factory()->make())->pluck('iso2')->all();

    expect($codes)->toBe(['IE', 'GB']);
});

test('ignores zones without a rate and zones whose method is disabled', function () {
    $uk = Country::factory()->create(['iso2' => 'GB', 'name' => 'United Kingdom']);
    $ireland = Country::factory()->create(['iso2' => 'IE', 'name' => 'Ireland']);
    $france = Country::factory()->create(['iso2' => 'FR', 'name' => 'France']);

    zoneWithRate('countries')->countries()->attach($uk);
    zoneWithRate('countries', enabled: false)->countries()->attach($ireland);
    ShippingZone::factory()->create(['type' => 'countries'])->countries()->attach($france);

    $codes = (new ShippingZoneCountries)->available(Cart::factory()->make())->pluck('iso2')->all();

    expect($codes)->toBe(['GB']);
});

test('a state zone contributes the country its states belong to', function () {
    $us = Country::factory()->create(['iso2' => 'US', 'name' => 'United States']);
    $state = State::factory()->create(['country_id' => $us->id]);

    zoneWithRate('states')->states()->attach($state);

    $codes = (new ShippingZoneCountries)->available(Cart::factory()->make())->pluck('iso2')->all();

    expect($codes)->toBe(['US']);
});

test('an unrestricted zone with a live rate means every country', function () {
    Country::factory()->count(3)->create();

    zoneWithRate('unrestricted');

    $countries = (new ShippingZoneCountries)->available(Cart::factory()->make());

    expect($countries)->toHaveCount(3);
});
