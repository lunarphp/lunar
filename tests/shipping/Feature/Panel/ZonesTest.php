<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Staff;
use Lunar\Core\Models\State;
use Lunar\Shipping\Models\ShippingExclusionList;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingRate;
use Lunar\Shipping\Models\ShippingZone;
use Lunar\Tests\Shipping\PanelTestCase;

uses(PanelTestCase::class)->group('shipping', 'shipping-panel');

beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');
    $this->gbp = Currency::factory()->create(['code' => 'GBP', 'decimal_places' => 2, 'default' => true]);
});

test('the zones index lists zones with their counts and first-party row actions', function () {
    $zone = ShippingZone::factory()->create(['name' => 'UK', 'type' => 'countries']);
    $method = ShippingMethod::factory()->create();
    ShippingRate::factory()->create(['shipping_zone_id' => $zone->id, 'shipping_method_id' => $method->id]);
    $zone->shippingExclusions()->sync([ShippingExclusionList::factory()->create()->id]);

    $this->get(route('panel.settings.shipping.zones.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('shipping::settings/shipping/zones/Index', false)
            ->has('zones.data', 1)
            ->where('zones.data.0.name', 'UK')
            ->where('zones.data.0.type', 'countries')
            ->where('zones.data.0.rates_count', 1)
            ->where('zones.data.0.exclusion_lists_count', 1)
            ->where('tableActions', fn ($actions) => collect($actions)->pluck('key')->all() === ['edit', 'delete'])
            ->where('zones.data.0._actions.edit', route('panel.settings.shipping.zones.edit', $zone))
            ->where('zones.data.0._actions.delete', route('panel.settings.shipping.zones.destroy', $zone))
            ->has('urls.store')
        );
});

test('a zone is created from name and type and redirects to its edit screen', function () {
    $this->post(route('panel.settings.shipping.zones.store'), ['name' => 'Europe', 'type' => 'countries'])
        ->assertRedirect(route('panel.settings.shipping.zones.edit', ShippingZone::first()))
        ->assertSessionHas('success');

    expect(ShippingZone::first()->type)->toBe('countries');
});

test('the zone type must be one the resolver understands', function () {
    $this->post(route('panel.settings.shipping.zones.store'), ['name' => 'Europe', 'type' => 'planets'])
        ->assertSessionHasErrors('type');
});

test('the edit screen carries coverage, rates in major units and the reference data', function () {
    $zone = ShippingZone::factory()->create(['name' => 'UK', 'type' => 'postcodes']);
    $country = Country::factory()->create();
    $zone->countries()->sync([$country->id]);
    $zone->postcodes()->create(['postcode' => 'NW1']);

    $method = ShippingMethod::factory()->create(['name' => 'Standard', 'data' => ['charge_by' => 'cart_total']]);
    $rate = ShippingRate::factory()->create(['shipping_zone_id' => $zone->id, 'shipping_method_id' => $method->id, 'enabled' => true]);
    $rate->prices()->create(['price' => 550, 'currency_id' => $this->gbp->id, 'min_quantity' => 1]);
    $rate->prices()->create(['price' => 250, 'currency_id' => $this->gbp->id, 'min_quantity' => 2000, 'customer_group_id' => CustomerGroup::factory()->create()->id]);

    $this->get(route('panel.settings.shipping.zones.edit', $zone))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('shipping::settings/shipping/zones/Edit', false)
            ->where('zone.name', 'UK')
            ->where('coverage.countries', [$country->id])
            ->where('coverage.postcodes', ['NW1'])
            ->has('rates', 1)
            ->where('rates.0.method_name', 'Standard')
            ->where('rates.0.base_price', '£5.50')
            ->where('rates.0.base_prices.GBP', 5.5)
            ->where('rates.0.tiers_count', 1)
            ->where('rates.0.tiers.0.min_quantity', fn ($value) => (float) $value === 20.0)
            ->where('rates.0.tiers.0.price', 2.5)
            ->where('rates.0.urls.update', route('panel.settings.shipping.zones.rates.update', [$zone, $rate]))
            ->where('methods.0.charge_by', 'cart_total')
            ->where('currencies.0.code', 'GBP')
            ->has('countries', 1)
            ->has('urls.ratesStore')
        );
});

test('updating a zone replaces its coverage and clears what the new type does not read', function () {
    $zone = ShippingZone::factory()->create(['type' => 'postcodes']);
    $zone->postcodes()->create(['postcode' => 'NW1']);
    [$uk, $fr] = Country::factory()->count(2)->create();
    $list = ShippingExclusionList::factory()->create();

    $this->put(route('panel.settings.shipping.zones.update', $zone), [
        'name' => 'Europe',
        'type' => 'countries',
        'countries' => [$uk->id, $fr->id],
        'exclusion_lists' => [$list->id],
    ])->assertRedirect()->assertSessionHas('success');

    $zone->refresh();

    expect($zone->name)->toBe('Europe')
        ->and($zone->countries()->count())->toBe(2)
        ->and($zone->postcodes()->count())->toBe(0)
        ->and($zone->shippingExclusions()->count())->toBe(1);
});

test('a state zone keeps its parent country and states', function () {
    $zone = ShippingZone::factory()->create(['type' => 'states']);
    $country = Country::factory()->create();
    $state = State::factory()->create(['country_id' => $country->id]);

    $this->put(route('panel.settings.shipping.zones.update', $zone), [
        'name' => $zone->name,
        'type' => 'states',
        'countries' => [$country->id],
        'states' => [$state->id],
    ])->assertRedirect();

    expect($zone->states()->pluck('state_id')->all())->toBe([$state->id])
        ->and($zone->countries()->pluck('country_id')->all())->toBe([$country->id]);
});

test('a rate is added to the zone with prices scaled per currency', function () {
    $jpy = Currency::factory()->create(['code' => 'JPY', 'decimal_places' => 0, 'default' => false]);
    $zone = ShippingZone::factory()->create();
    $method = ShippingMethod::factory()->create(['data' => ['charge_by' => 'cart_total']]);

    $this->post(route('panel.settings.shipping.zones.rates.store', $zone), [
        'shipping_method_id' => $method->id,
        'enabled' => true,
        'base_prices' => ['GBP' => '4.99', 'JPY' => '700'],
        'tiers' => [['customer_group_id' => null, 'currency_code' => 'GBP', 'min_quantity' => '50', 'price' => '0']],
    ])->assertRedirect()->assertSessionHas('success');

    $rate = $zone->rates()->first();
    $base = $rate->basePrices()->get()->keyBy('currency_id');

    expect($base[$this->gbp->id]->price)->toBe(499)
        ->and($base[$jpy->id]->price)->toBe(700)
        ->and($rate->priceBreaks()->first()->min_quantity)->toBe(5000)
        ->and($rate->priceBreaks()->first()->price)->toBe(0);
});

test('a rate needs a base price in the default currency and whole-number weight tiers', function () {
    $zone = ShippingZone::factory()->create();
    $method = ShippingMethod::factory()->create(['data' => ['charge_by' => 'weight'], 'weight_unit' => 'kg']);

    $this->post(route('panel.settings.shipping.zones.rates.store', $zone), [
        'shipping_method_id' => $method->id,
        'base_prices' => ['GBP' => ''],
        'tiers' => [['currency_code' => 'GBP', 'min_quantity' => '2.5', 'price' => '1']],
    ])->assertSessionHasErrors(['base_prices.GBP', 'tiers.0.min_quantity']);
});

test('a rate is updated, disabled and removed through the zone it belongs to', function () {
    $zone = ShippingZone::factory()->create();
    $otherZone = ShippingZone::factory()->create();
    $method = ShippingMethod::factory()->create(['data' => ['charge_by' => 'cart_total']]);
    $rate = ShippingRate::factory()->create(['shipping_zone_id' => $zone->id, 'shipping_method_id' => $method->id, 'enabled' => true]);
    $rate->prices()->create(['price' => 100, 'currency_id' => $this->gbp->id, 'min_quantity' => 1]);

    $this->put(route('panel.settings.shipping.zones.rates.update', [$zone, $rate]), [
        'shipping_method_id' => $method->id,
        'enabled' => false,
        'base_prices' => ['GBP' => '6'],
        'tiers' => [],
    ])->assertRedirect()->assertSessionHas('success');

    expect($rate->refresh()->enabled)->toBeFalsy()
        ->and($rate->basePrices()->first()->price)->toBe(600);

    $this->delete(route('panel.settings.shipping.zones.rates.destroy', [$otherZone, $rate]))->assertNotFound();

    $this->delete(route('panel.settings.shipping.zones.rates.destroy', [$zone, $rate]))->assertRedirect();

    expect(ShippingRate::find($rate->id))->toBeNull();
});

test('a zone can be deleted', function () {
    $zone = ShippingZone::factory()->create();

    $this->delete(route('panel.settings.shipping.zones.destroy', $zone))
        ->assertRedirect(route('panel.settings.shipping.zones.index'))
        ->assertSessionHas('success');

    expect(ShippingZone::find($zone->id))->toBeNull();
});

test('a tier without a customer group must start above the base price', function () {
    $zone = ShippingZone::factory()->create();
    $bySpend = ShippingMethod::factory()->create(['data' => ['charge_by' => 'cart_total']]);
    $byWeight = ShippingMethod::factory()->create(['data' => ['charge_by' => 'weight'], 'weight_unit' => 'kg']);

    $this->post(route('panel.settings.shipping.zones.rates.store', $zone), [
        'shipping_method_id' => $bySpend->id,
        'base_prices' => ['GBP' => '5'],
        'tiers' => [
            ['customer_group_id' => null, 'currency_code' => 'GBP', 'min_quantity' => '0.01', 'price' => '1'],
            ['customer_group_id' => null, 'currency_code' => 'GBP', 'min_quantity' => '0.02', 'price' => '1'],
        ],
    ])->assertSessionHasErrors('tiers.0.min_quantity')->assertSessionDoesntHaveErrors('tiers.1.min_quantity');

    $this->post(route('panel.settings.shipping.zones.rates.store', $zone), [
        'shipping_method_id' => $byWeight->id,
        'base_prices' => ['GBP' => '5'],
        'tiers' => [['customer_group_id' => null, 'currency_code' => 'GBP', 'min_quantity' => '1', 'price' => '1']],
    ])->assertSessionHasErrors('tiers.0.min_quantity');
});

test('a customer group tier from zero is shown on the edit screen and removed with the tier', function () {
    $group = CustomerGroup::factory()->create();
    $zone = ShippingZone::factory()->create();
    $method = ShippingMethod::factory()->create(['data' => ['charge_by' => 'weight'], 'weight_unit' => 'kg']);

    $this->post(route('panel.settings.shipping.zones.rates.store', $zone), [
        'shipping_method_id' => $method->id,
        'base_prices' => ['GBP' => '5'],
        'tiers' => [['customer_group_id' => $group->id, 'currency_code' => 'GBP', 'min_quantity' => '0', 'price' => '3']],
    ])->assertSessionHasNoErrors();

    $rate = $zone->rates()->first();

    $this->get(route('panel.settings.shipping.zones.edit', $zone))
        ->assertInertia(fn (Assert $page) => $page
            ->where('rates.0.base_prices', ['GBP' => 5])
            ->where('rates.0.tiers_count', 1)
            ->where('rates.0.tiers.0.customer_group_id', $group->id)
            ->where('rates.0.tiers.0.min_quantity', 0)
        );

    $this->put(route('panel.settings.shipping.zones.rates.update', [$zone, $rate]), [
        'shipping_method_id' => $method->id,
        'base_prices' => ['GBP' => '5'],
        'tiers' => [],
    ])->assertSessionHasNoErrors();

    expect($rate->prices()->count())->toBe(1)
        ->and($rate->prices()->first()->customer_group_id)->toBeNull();
});

test('a zone has one rate per shipping method', function () {
    $zone = ShippingZone::factory()->create();
    $method = ShippingMethod::factory()->create(['data' => ['charge_by' => 'cart_total']]);
    $rate = ShippingRate::factory()->create(['shipping_zone_id' => $zone->id, 'shipping_method_id' => $method->id]);
    ShippingRate::factory()->create(['shipping_zone_id' => ShippingZone::factory()->create()->id, 'shipping_method_id' => $method->id]);

    $payload = ['shipping_method_id' => $method->id, 'base_prices' => ['GBP' => '5'], 'tiers' => []];

    $this->post(route('panel.settings.shipping.zones.rates.store', $zone), $payload)
        ->assertSessionHasErrors('shipping_method_id');

    $this->put(route('panel.settings.shipping.zones.rates.update', [$zone, $rate]), $payload)
        ->assertSessionHasNoErrors();
});

test('blank postcode lines are ignored', function () {
    $zone = ShippingZone::factory()->create(['type' => 'postcodes']);
    $country = Country::factory()->create();

    $this->put(route('panel.settings.shipping.zones.update', $zone), [
        'name' => $zone->name,
        'type' => 'postcodes',
        'countries' => [$country->id],
        'postcodes' => ['AB1', '', 'CD2', ''],
    ])->assertSessionHasNoErrors();

    expect($zone->postcodes()->pluck('postcode')->sort()->values()->all())->toBe(['AB1', 'CD2']);
});

test('state and postcode zones take exactly one country, and states must belong to it', function (string $type) {
    $zone = ShippingZone::factory()->create(['type' => $type]);
    [$uk, $us] = Country::factory()->count(2)->create();

    $this->put(route('panel.settings.shipping.zones.update', $zone), [
        'name' => $zone->name,
        'type' => $type,
        'countries' => [$uk->id, $us->id],
    ])->assertSessionHasErrors('countries');

    $this->put(route('panel.settings.shipping.zones.update', $zone), [
        'name' => $zone->name,
        'type' => $type,
        'countries' => [],
    ])->assertSessionHasErrors('countries');
})->with(['states', 'postcodes']);

test('a state zone rejects states from another country', function () {
    $zone = ShippingZone::factory()->create(['type' => 'states']);
    [$uk, $us] = Country::factory()->count(2)->create();
    $texas = State::factory()->create(['country_id' => $us->id]);

    $this->put(route('panel.settings.shipping.zones.update', $zone), [
        'name' => $zone->name,
        'type' => 'states',
        'countries' => [$uk->id],
        'states' => [$texas->id],
    ])->assertSessionHasErrors('states.0');
});
