<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\State;
use Lunar\Shipping\Contracts\Actions\ShippingZones\CreatesShippingZone;
use Lunar\Shipping\Contracts\Actions\ShippingZones\DeletesShippingZone;
use Lunar\Shipping\Contracts\Actions\ShippingZones\UpdatesShippingZone;
use Lunar\Shipping\Models\ShippingExclusionList;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingRate;
use Lunar\Shipping\Models\ShippingZone;
use Lunar\Tests\Shipping\TestCase;

uses(TestCase::class, RefreshDatabase::class)->group('shipping', 'shipping-actions');

test('a zone can be created from its own columns', function () {
    $zone = app(CreatesShippingZone::class)->execute(['name' => 'UK', 'type' => 'countries']);

    expect($zone)->toBeInstanceOf(ShippingZone::class)
        ->and($zone->name)->toBe('UK')
        ->and($zone->type)->toBe('countries');
});

test('country coverage is replaced wholesale and an absent key leaves it alone', function () {
    $zone = ShippingZone::factory()->create(['type' => 'countries']);
    [$uk, $fr, $de] = Country::factory()->count(3)->create();
    $zone->countries()->sync([$uk->id, $fr->id]);

    app(UpdatesShippingZone::class)->execute($zone, ['name' => 'Europe', 'countries' => [$fr->id, $de->id]]);

    expect($zone->refresh()->name)->toBe('Europe')
        ->and($zone->countries()->pluck('country_id')->sort()->values()->all())->toBe(collect([$fr->id, $de->id])->sort()->values()->all());

    app(UpdatesShippingZone::class)->execute($zone, ['name' => 'Europe again']);

    expect($zone->countries()->count())->toBe(2);
});

test('postcodes are normalised, deduplicated, and keep the single parent country', function () {
    $zone = ShippingZone::factory()->create(['type' => 'postcodes']);
    $country = Country::factory()->create();

    app(UpdatesShippingZone::class)->execute($zone, [
        'countries' => [$country->id],
        'postcodes' => ['NW1 8QQ', 'nw18qq', 'NW1 8QQ', '', 'SW*'],
    ]);

    expect($zone->postcodes()->pluck('postcode')->sort()->values()->all())->toBe(['NW18QQ', 'SW*', 'nw18qq'])
        ->and($zone->countries()->pluck('country_id')->all())->toBe([$country->id]);
});

test('switching the zone type clears coverage the new type does not read', function () {
    $zone = ShippingZone::factory()->create(['type' => 'postcodes']);
    $country = Country::factory()->create();
    $state = State::factory()->create(['country_id' => $country->id]);
    $zone->countries()->sync([$country->id]);
    $zone->postcodes()->create(['postcode' => 'NW1']);

    app(UpdatesShippingZone::class)->execute($zone, ['type' => 'states', 'states' => [$state->id]]);

    expect($zone->postcodes()->count())->toBe(0)
        ->and($zone->states()->pluck('state_id')->all())->toBe([$state->id])
        ->and($zone->countries()->count())->toBe(1);

    app(UpdatesShippingZone::class)->execute($zone, ['type' => 'unrestricted']);

    expect($zone->states()->count())->toBe(0)
        ->and($zone->countries()->count())->toBe(0);
});

test('exclusion lists are synced onto the zone', function () {
    $zone = ShippingZone::factory()->create();
    [$a, $b] = ShippingExclusionList::factory()->count(2)->create();
    $zone->shippingExclusions()->sync([$a->id]);

    app(UpdatesShippingZone::class)->execute($zone, ['exclusion_lists' => [$b->id]]);

    expect($zone->shippingExclusions()->get()->pluck('id')->all())->toBe([$b->id]);
});

test('deleting a zone removes its rates and coverage', function () {
    $zone = ShippingZone::factory()->create(['type' => 'countries']);
    $zone->countries()->sync([Country::factory()->create()->id]);
    $method = ShippingMethod::factory()->create();
    ShippingRate::factory()->create(['shipping_zone_id' => $zone->id, 'shipping_method_id' => $method->id]);

    app(DeletesShippingZone::class)->execute($zone);

    expect(ShippingZone::find($zone->id))->toBeNull()
        ->and(ShippingRate::where('shipping_zone_id', $zone->id)->count())->toBe(0);
});
