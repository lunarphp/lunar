<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Region;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');
});

test('the regions index renders with reference names', function () {
    $region = Region::factory()->create(['name' => 'UK', 'default' => true]);

    $this->get(route('panel.settings.regions.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/regions/Index')
            ->has('regions.data', 1)
            ->where('regions.data.0.name', 'UK')
            ->where('regions.data.0.default', true)
            ->whereType('regions.data.0.default', 'boolean')
            ->has('regions.data.0.channel')
            ->has('channels')
            ->has('currencies')
            ->has('languages')
            ->has('urls.store')
        );
});

test('a region can be created and redirects to its edit screen', function () {
    $channel = Channel::factory()->create();
    $currency = Currency::factory()->create();
    $language = Language::factory()->create();

    $this->post(route('panel.settings.regions.store'), [
        'name' => 'Europe',
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
        'language_id' => $language->id,
    ])->assertRedirect()
        ->assertSessionHas('success');

    $region = Region::where('handle', 'europe')->first();

    expect($region)->not->toBeNull();
    expect($region->channel_id)->toBe($channel->id);
    expect($region->default)->toBeFalse();
});

test('a colliding auto-generated handle is rejected as a validation error', function () {
    Region::factory()->create(['name' => 'Europe', 'handle' => 'europe']);
    $channel = Channel::factory()->create();
    $currency = Currency::factory()->create();
    $language = Language::factory()->create();

    $this->post(route('panel.settings.regions.store'), [
        'name' => 'Europe',
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
        'language_id' => $language->id,
    ])->assertSessionHasErrors('handle');

    expect(Region::count())->toBe(1);
});

test('the region edit screen renders with coverage and reference data', function () {
    $region = Region::factory()->create(['name' => 'UK']);
    $country = Country::factory()->create();
    $region->countries()->attach($country->id);

    $this->get(route('panel.settings.regions.edit', $region))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/regions/Edit')
            ->where('region.name', 'UK')
            ->where('region.countries.0', $country->id)
            ->has('taxZones')
            ->has('countries')
            ->where('hasOrderHistory', false)
        );
});

test('updating a region syncs its countries', function () {
    $region = Region::factory()->create();
    $kept = Country::factory()->create();
    $removed = Country::factory()->create();
    $added = Country::factory()->create();
    $region->countries()->attach([$kept->id, $removed->id]);

    $this->put(route('panel.settings.regions.update', $region), [
        'name' => $region->name,
        'handle' => $region->handle,
        'channel_id' => $region->channel_id,
        'currency_id' => $region->currency_id,
        'language_id' => $region->language_id,
        'countries' => [$kept->id, $added->id],
    ])->assertRedirect()
        ->assertSessionHas('success');

    expect($region->countries()->pluck('country_id')->sort()->values()->all())
        ->toBe(collect([$kept->id, $added->id])->sort()->values()->all());
});

test('updating a region to default un-defaults whichever region was default', function () {
    $default = Region::factory()->create(['default' => true]);
    $region = Region::factory()->create(['default' => false]);

    $this->put(route('panel.settings.regions.update', $region), [
        'name' => $region->name,
        'handle' => $region->handle,
        'channel_id' => $region->channel_id,
        'currency_id' => $region->currency_id,
        'language_id' => $region->language_id,
        'default' => true,
    ])->assertRedirect();

    expect($default->fresh()->default)->toBeFalse();
    expect($region->fresh()->default)->toBeTrue();
    expect(Region::where('default', true)->count())->toBe(1);
});

test('unsetting default on the default region is rejected with a flash error', function () {
    $region = Region::factory()->create(['default' => true]);

    $this->from(route('panel.settings.regions.edit', $region))
        ->put(route('panel.settings.regions.update', $region), [
            'name' => $region->name,
            'handle' => $region->handle,
            'channel_id' => $region->channel_id,
            'currency_id' => $region->currency_id,
            'language_id' => $region->language_id,
            'default' => false,
        ])->assertRedirect(route('panel.settings.regions.edit', $region))
        ->assertSessionHas('error', __('panel::regions.default_unset_blocked'));

    expect($region->fresh()->default)->toBeTrue();
});

test('the default region cannot be deleted and shows a flash error', function () {
    $region = Region::factory()->create(['default' => true]);

    $this->from(route('panel.settings.regions.edit', $region))
        ->delete(route('panel.settings.regions.destroy', $region))
        ->assertRedirect(route('panel.settings.regions.edit', $region))
        ->assertSessionHas('error', __('panel::regions.delete_blocked_default'));

    expect(Region::find($region->id))->not->toBeNull();
});

test('a region with order history cannot be deleted and shows a flash error', function () {
    $region = Region::factory()->create(['default' => false]);
    Order::factory()->create(['region_id' => $region->id]);

    $this->from(route('panel.settings.regions.edit', $region))
        ->delete(route('panel.settings.regions.destroy', $region))
        ->assertRedirect(route('panel.settings.regions.edit', $region))
        ->assertSessionHas('error', __('panel::regions.delete_blocked'));

    expect(Region::find($region->id))->not->toBeNull();
});

test('a region with no order history can be deleted', function () {
    $region = Region::factory()->create(['default' => false]);
    $region->countries()->attach(Country::factory()->create()->id);

    $this->delete(route('panel.settings.regions.destroy', $region))
        ->assertRedirect(route('panel.settings.regions.index'))
        ->assertSessionHas('success');

    expect(Region::find($region->id))->toBeNull();
});
