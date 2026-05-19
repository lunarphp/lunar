<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Lunar\Models\Contracts\Country as CountryContract;
use Lunar\Models\Country;
use Lunar\Models\State;
use Lunar\Shipping\DataTransferObjects\PostcodeLookup;
use Lunar\Shipping\Facades\Postcode;
use Lunar\Shipping\Interfaces\PostcodeResolverInterface;
use Lunar\Shipping\Managers\PostcodeManager;
use Lunar\Shipping\Models\ShippingZone;
use Lunar\Shipping\Resolvers\ShippingZoneResolver;
use Lunar\Tests\Shipping\Stubs\Resolvers\TestCustomPostcodeResolver;
use Lunar\Tests\Shipping\TestCase;

uses(TestCase::class)
    ->group('shipping', 'shipping-zone');
uses(RefreshDatabase::class);

// Reset the PostcodeManager singleton between tests so facade-registered resolvers from one
// test don't leak into the next. The service provider's register() closure re-runs on next
// resolve, rebuilding the manager with only the default PostcodeResolver.
beforeEach(function () {
    $this->app->forgetInstance(PostcodeManager::class);
});

test('can fetch shipping zones by country', function () {
    $countryA = Country::factory()->create();
    $countryB = Country::factory()->create();

    $shippingZoneA = ShippingZone::factory()->create([
        'type' => 'countries',
    ]);

    $shippingZoneB = ShippingZone::factory()->create([
        'type' => 'countries',
    ]);

    $shippingZoneA->countries()->attach($countryA);
    $shippingZoneB->countries()->attach($countryB);

    expect($shippingZoneA->refresh()->countries)->toHaveCount(1);

    $zones = (new ShippingZoneResolver)->country($countryA)->get();

    expect($zones)->toHaveCount(1);

    expect($zones->first()->id)->toEqual($shippingZoneA->id);
});

test('can fetch shipping zones by state', function () {
    $countryA = Country::factory()->create();
    $countryB = Country::factory()->create();

    $stateA = State::factory()->create([
        'country_id' => $countryA->id,
    ]);

    $stateB = State::factory()->create([
        'country_id' => $countryB->id,
    ]);

    $shippingZoneA = ShippingZone::factory()->create([
        'type' => 'states',
    ]);

    $shippingZoneB = ShippingZone::factory()->create([
        'type' => 'countries',
    ]);

    $shippingZoneA->states()->attach($stateA);
    $shippingZoneB->states()->attach($stateB);

    expect($shippingZoneA->refresh()->states)->toHaveCount(1);

    $zones = (new ShippingZoneResolver)->state($stateA)->get();

    expect($zones)->toHaveCount(1);

    expect($zones->first()->id)->toEqual($shippingZoneA->id);
});

test('doesnt fetch postcode shipping zones by country', function () {
    $countryA = Country::factory()->create();

    $shippingZoneA = ShippingZone::factory()->create([
        'type' => 'postcodes',
    ]);

    $shippingZoneA->countries()->attach($countryA);

    expect($shippingZoneA->refresh()->countries)->toHaveCount(1);

    $zones = (new ShippingZoneResolver)->country($countryA)->get();

    expect($zones)->toBeEmpty();
});

test('can fetch zone by postcode lookup', function () {
    $country = Country::factory()->create();

    $shippingZone = ShippingZone::factory()->create([
        'type' => 'postcodes',
    ]);

    $shippingZone->countries()->attach($country);

    $shippingZone->postcodes()->create([
        'postcode' => 'ABC',
    ]);

    expect($shippingZone->refresh()->countries)->toHaveCount(1);
    expect($shippingZone->refresh()->postcodes)->toHaveCount(1);

    $postcode = new PostcodeLookup(
        $country,
        'ABC 123'
    );

    $zones = (new ShippingZoneResolver)->postcode($postcode)->get();

    expect($zones)->toHaveCount(1);

    expect($zones->first()->id)->toEqual($shippingZone->id);
});

test('a resolver registered via the Postcode facade takes precedence over the default', function () {
    $country = Country::factory()->create();

    $shippingZone = ShippingZone::factory()->create([
        'type' => 'postcodes',
    ]);

    $shippingZone->countries()->attach($country);

    $shippingZone->postcodes()->createMany([
        ['postcode' => '390*'],
        ['postcode' => '391*'],
    ]);

    $unmatchedZone = ShippingZone::factory()->create([
        'type' => 'postcodes',
    ]);

    $unmatchedZone->countries()->attach($country);

    $unmatchedZone->postcodes()->create([
        'postcode' => '393*',
    ]);

    Postcode::addResolver(TestCustomPostcodeResolver::class);

    $zones = (new ShippingZoneResolver)
        ->postcode(new PostcodeLookup($country, '39100'))
        ->get();

    expect($zones)->toHaveCount(1);
    expect($zones->first()->id)->toEqual($shippingZone->id);
})->group('shipping-postcode');

test('the last-registered matching resolver wins over earlier custom resolvers', function () {
    $country = Country::factory()->create();

    $shippingZone = ShippingZone::factory()->create([
        'type' => 'postcodes',
    ]);
    $shippingZone->countries()->attach($country);
    $shippingZone->postcodes()->create(['postcode' => 'LAST-WON']);

    $firstCustom = new class implements PostcodeResolverInterface
    {
        public function supportsCountry(CountryContract $country): bool
        {
            return true;
        }

        public function getParts(string $postcode, CountryContract $country): Collection
        {
            return collect(['FIRST-WON']);
        }
    };

    $secondCustom = new class implements PostcodeResolverInterface
    {
        public function supportsCountry(CountryContract $country): bool
        {
            return true;
        }

        public function getParts(string $postcode, CountryContract $country): Collection
        {
            return collect(['LAST-WON']);
        }
    };

    Postcode::addResolver($firstCustom);
    Postcode::addResolver($secondCustom);

    $zones = (new ShippingZoneResolver)
        ->postcode(new PostcodeLookup($country, 'irrelevant'))
        ->get();

    expect($zones)->toHaveCount(1);
    expect($zones->first()->id)->toEqual($shippingZone->id);
})->group('shipping-postcode');

test('a resolver whose supportsCountry returns false is skipped and the default handles the lookup', function () {
    $country = Country::factory()->create(['iso2' => 'GB']);

    $shippingZone = ShippingZone::factory()->create([
        'type' => 'postcodes',
    ]);
    $shippingZone->countries()->attach($country);
    // Default resolver's UK parsing will produce 'SW1A', 'SW', 'S' etc. Match on 'SW'.
    $shippingZone->postcodes()->create(['postcode' => 'SW']);

    $usOnly = new class implements PostcodeResolverInterface
    {
        public function supportsCountry(CountryContract $country): bool
        {
            return $country->iso2 === 'US';
        }

        public function getParts(string $postcode, CountryContract $country): Collection
        {
            return collect(['NEVER-CALLED']);
        }
    };

    Postcode::addResolver($usOnly);

    $zones = (new ShippingZoneResolver)
        ->postcode(new PostcodeLookup($country, 'SW1A 1AA'))
        ->get();

    expect($zones)->toHaveCount(1);
    expect($zones->first()->id)->toEqual($shippingZone->id);
})->group('shipping-postcode');
