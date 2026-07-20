<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Staff;
use Lunar\Core\Models\State;
use Lunar\Core\Models\TaxClass;
use Lunar\Core\Models\TaxRate;
use Lunar\Core\Models\TaxZone;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');
});

test('the tax zones index renders with the real zone list', function () {
    TaxZone::factory()->create(['name' => 'UK', 'zone_type' => 'country', 'default' => true, 'active' => true]);
    TaxZone::factory()->create(['name' => 'US', 'zone_type' => 'state', 'default' => false, 'active' => false]);

    $this->get(route('panel.settings.tax-zones.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/tax-zones/Index')
            ->has('taxZones.data', 2)
            ->where('taxZones.data.0.name', 'UK')
            ->where('taxZones.data.0.default', true)
            ->where('taxZones.data.0.zone_type', 'country')
            ->where('taxZones.data.1.name', 'US')
            ->where('taxZones.data.1.active', false)
            ->has('urls.store')
        );
});

test('tax zones carry first-party row actions, with delete omitted for the default zone', function () {
    TaxZone::factory()->create(['name' => 'UK', 'default' => true]);
    TaxZone::factory()->create(['name' => 'US', 'default' => false]);

    $this->get(route('panel.settings.tax-zones.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('tableActions', fn ($actions) => collect($actions)->pluck('key')->all() === ['edit', 'delete'])
            ->where('taxZones.data.0._actions', fn ($actions) => isset($actions['edit']) && ! isset($actions['delete']))
            ->where('taxZones.data.1._actions', fn ($actions) => isset($actions['edit'], $actions['delete']))
        );
});

test('a tax zone can be created and redirects to its edit screen', function () {
    $this->post(route('panel.settings.tax-zones.store'), [
        'name' => 'Europe',
        'zone_type' => 'country',
    ])->assertRedirect()
        ->assertSessionHas('success');

    $taxZone = TaxZone::where('name', 'Europe')->first();

    expect($taxZone)->not->toBeNull();
    expect($taxZone->zone_type)->toBe('country');
    expect($taxZone->active)->toBeTrue();
    expect($taxZone->default)->toBeFalse();
});

test('zone type must be valid', function () {
    $this->post(route('panel.settings.tax-zones.store'), [
        'name' => 'Europe',
        'zone_type' => 'planets',
    ])->assertSessionHasErrors('zone_type');
});

test('the tax zone edit screen renders with coverage, rates, and reference data', function () {
    $taxZone = TaxZone::factory()->create(['name' => 'UK', 'zone_type' => 'country']);
    $country = Country::factory()->create(['name' => 'United Kingdom']);
    $taxZone->countries()->create(['country_id' => $country->id]);

    $taxClass = TaxClass::factory()->create(['name' => 'Standard']);
    $rate = TaxRate::factory()->create(['tax_zone_id' => $taxZone->id, 'name' => 'VAT', 'priority' => 1]);
    $rate->taxRateAmounts()->create(['tax_class_id' => $taxClass->id, 'percentage' => 20]);

    $this->get(route('panel.settings.tax-zones.edit', $taxZone))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/tax-zones/Edit')
            ->where('taxZone.name', 'UK')
            ->where('coverage.countries.0', $country->id)
            ->has('rates', 1)
            ->where('rates.0.name', 'VAT')
            ->where('rates.0.amounts.'.$taxClass->id, 20)
            ->has('taxClasses', 1)
            ->has('countries', 1)
            ->has('urls.update')
        );
});

test('updating a tax zone syncs its country coverage', function () {
    $taxZone = TaxZone::factory()->create(['name' => 'Europe', 'zone_type' => 'country']);
    $kept = Country::factory()->create();
    $removed = Country::factory()->create();
    $added = Country::factory()->create();

    $taxZone->countries()->createMany([
        ['country_id' => $kept->id],
        ['country_id' => $removed->id],
    ]);

    $this->put(route('panel.settings.tax-zones.update', $taxZone), [
        'name' => 'Europe',
        'zone_type' => 'country',
        'countries' => [$kept->id, $added->id],
    ])->assertRedirect()
        ->assertSessionHas('success');

    expect($taxZone->countries()->pluck('country_id')->sort()->values()->all())
        ->toBe(collect([$kept->id, $added->id])->sort()->values()->all());
});

test('updating a tax zone syncs states, postcodes, and customer groups', function () {
    $taxZone = TaxZone::factory()->create(['zone_type' => 'state']);
    $country = Country::factory()->create();
    $state = State::factory()->create(['country_id' => $country->id]);
    $group = CustomerGroup::factory()->create();

    $this->put(route('panel.settings.tax-zones.update', $taxZone), [
        'name' => $taxZone->name,
        'zone_type' => 'state',
        'states' => [$state->id],
        'postcodes' => [['country_id' => $country->id, 'postcode' => 'SW1A *']],
        'customer_groups' => [$group->id],
    ])->assertRedirect();

    expect($taxZone->states()->pluck('state_id')->all())->toBe([$state->id]);
    expect($taxZone->postcodes()->pluck('postcode')->all())->toBe(['SW1A *']);
    expect($taxZone->customerGroups()->pluck('customer_group_id')->all())->toBe([$group->id]);
});

test('updating a tax zone syncs rates and their per-class amounts', function () {
    $taxZone = TaxZone::factory()->create();
    $standard = TaxClass::factory()->create(['name' => 'Standard']);
    $reduced = TaxClass::factory()->create(['name' => 'Reduced']);

    $existing = TaxRate::factory()->create(['tax_zone_id' => $taxZone->id, 'name' => 'VAT', 'priority' => 1]);
    $existing->taxRateAmounts()->create(['tax_class_id' => $standard->id, 'percentage' => 17.5]);

    $stale = TaxRate::factory()->create(['tax_zone_id' => $taxZone->id, 'name' => 'Old levy', 'priority' => 2]);

    $this->put(route('panel.settings.tax-zones.update', $taxZone), [
        'name' => $taxZone->name,
        'zone_type' => $taxZone->zone_type,
        'rates' => [
            [
                'id' => $existing->id,
                'name' => 'VAT',
                'priority' => 1,
                'amounts' => [$standard->id => 20, $reduced->id => 5],
            ],
            [
                'name' => 'Eco levy',
                'priority' => 2,
                'amounts' => [$standard->id => 2],
            ],
        ],
    ])->assertRedirect();

    expect(TaxRate::find($stale->id))->toBeNull();
    expect($taxZone->taxRates()->count())->toBe(2);

    $existing->refresh();
    expect((float) $existing->taxRateAmounts()->where('tax_class_id', $standard->id)->value('percentage'))->toBe(20.0);
    expect((float) $existing->taxRateAmounts()->where('tax_class_id', $reduced->id)->value('percentage'))->toBe(5.0);

    $new = $taxZone->taxRates()->where('name', 'Eco levy')->first();
    expect($new)->not->toBeNull();
    expect((float) $new->taxRateAmounts()->where('tax_class_id', $standard->id)->value('percentage'))->toBe(2.0);
});

test('rate percentages must be within 0-100', function () {
    $taxZone = TaxZone::factory()->create();
    $taxClass = TaxClass::factory()->create();

    $this->put(route('panel.settings.tax-zones.update', $taxZone), [
        'name' => $taxZone->name,
        'zone_type' => $taxZone->zone_type,
        'rates' => [
            ['name' => 'VAT', 'priority' => 1, 'amounts' => [$taxClass->id => 250]],
        ],
    ])->assertSessionHasErrors();
});

test('unsetting default on the default zone is rejected with a flash error', function () {
    $taxZone = TaxZone::factory()->create(['default' => true]);

    $this->from(route('panel.settings.tax-zones.edit', $taxZone))
        ->put(route('panel.settings.tax-zones.update', $taxZone), [
            'name' => $taxZone->name,
            'zone_type' => $taxZone->zone_type,
            'default' => false,
        ])->assertRedirect(route('panel.settings.tax-zones.edit', $taxZone))
        ->assertSessionHas('error', __('panel::tax_zones.default_unset_blocked'));

    expect($taxZone->fresh()->default)->toBeTrue();
});

test('the default zone cannot be deleted and shows a flash error', function () {
    $taxZone = TaxZone::factory()->create(['default' => true]);

    $this->from(route('panel.settings.tax-zones.edit', $taxZone))
        ->delete(route('panel.settings.tax-zones.destroy', $taxZone))
        ->assertRedirect(route('panel.settings.tax-zones.edit', $taxZone))
        ->assertSessionHas('error', __('panel::tax_zones.delete_blocked_default'));

    expect(TaxZone::find($taxZone->id))->not->toBeNull();
});

test('a non-default zone can be deleted along with its coverage and rates', function () {
    $taxZone = TaxZone::factory()->create(['default' => false]);
    $country = Country::factory()->create();
    $taxZone->countries()->create(['country_id' => $country->id]);
    $rate = TaxRate::factory()->create(['tax_zone_id' => $taxZone->id]);

    $this->delete(route('panel.settings.tax-zones.destroy', $taxZone))
        ->assertRedirect(route('panel.settings.tax-zones.index'))
        ->assertSessionHas('success');

    expect(TaxZone::find($taxZone->id))->toBeNull();
    expect(TaxRate::find($rate->id))->toBeNull();
});
