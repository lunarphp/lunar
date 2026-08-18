<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Staff;
use Lunar\Core\Models\State;
use Lunar\Core\Models\TaxZone;
use Lunar\Core\Models\TaxZoneState;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');
});

test('the countries index renders with a paginated country list', function () {
    Country::factory()->create(['name' => 'United Kingdom', 'iso2' => 'GB', 'iso3' => 'GBR']);
    Country::factory()->create(['name' => 'Germany', 'iso2' => 'DE', 'iso3' => 'DEU']);

    $this->get(route('panel.settings.countries.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/countries/Index')
            ->has('countries.data', 2)
            ->where('countries.data.0.name', 'Germany')
            ->where('countries.data.1.name', 'United Kingdom')
            ->where('countries.total', 2)
        );
});

test('the countries index can be searched by name or ISO code', function () {
    Country::factory()->create(['name' => 'United Kingdom', 'iso2' => 'GB', 'iso3' => 'GBR']);
    Country::factory()->create(['name' => 'Germany', 'iso2' => 'DE', 'iso3' => 'DEU']);

    $this->get(route('panel.settings.countries.index', ['q' => 'GBR']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('countries.data', 1)
            ->where('countries.data.0.name', 'United Kingdom')
        );

    $this->get(route('panel.settings.countries.index', ['q' => 'germ']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('countries.data', 1)
            ->where('countries.data.0.name', 'Germany')
        );
});

test('countries carry first-party row actions, with delete omitted for countries with states', function () {
    $withStates = Country::factory()->create(['name' => 'Germany']);
    State::factory()->create(['country_id' => $withStates->id]);
    Country::factory()->create(['name' => 'United Kingdom']);

    $this->get(route('panel.settings.countries.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('tableActions', fn ($actions) => collect($actions)->pluck('key')->all() === ['edit', 'delete'])
            ->where('countries.data.0._actions', fn ($actions) => isset($actions['edit']) && ! isset($actions['delete']))
            ->where('countries.data.1._actions', fn ($actions) => isset($actions['edit'], $actions['delete']))
        );
});

test('the country edit screen renders with the country and its states', function () {
    $country = Country::factory()->create(['name' => 'United Kingdom', 'iso2' => 'GB', 'iso3' => 'GBR']);
    State::factory()->create(['country_id' => $country->id, 'name' => 'Scotland', 'code' => 'SCT']);

    $this->get(route('panel.settings.countries.edit', $country))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/countries/Edit')
            ->where('country.id', $country->id)
            ->where('country.name', 'United Kingdom')
            ->where('country.iso2', 'GB')
            ->where('country.iso3', 'GBR')
            ->has('states', 1)
            ->where('states.0.name', 'Scotland')
            ->where('states.0.inTaxZone', false)
            ->has('urls.storeState')
        );
});

test('a country can be updated', function () {
    $country = Country::factory()->create(['name' => 'United Kingdom', 'iso2' => 'GB', 'iso3' => 'GBR']);

    $this->put(route('panel.settings.countries.update', $country), [
        'name' => 'Great Britain',
        'iso2' => 'gb',
        'iso3' => 'gbr',
    ])->assertRedirect(route('panel.settings.countries.index'))
        ->assertSessionHas('success');

    $country->refresh();

    expect($country->name)->toBe('Great Britain');
    expect($country->iso2)->toBe('GB');
    expect($country->iso3)->toBe('GBR');
});

test('a state can be added to a country', function () {
    $country = Country::factory()->create();

    $this->post(route('panel.settings.countries.states.store', $country), [
        'name' => 'Bavaria',
        'code' => 'BY',
    ])->assertRedirect()
        ->assertSessionHas('success');

    expect($country->states()->where('name', 'Bavaria')->where('code', 'BY')->exists())->toBeTrue();
});

test('a state requires a name and code', function () {
    $country = Country::factory()->create();

    $this->post(route('panel.settings.countries.states.store', $country), [])
        ->assertSessionHasErrors(['name', 'code']);
});

test('a state can be removed from a country', function () {
    $country = Country::factory()->create();
    $state = State::factory()->create(['country_id' => $country->id]);

    $this->delete(route('panel.settings.countries.states.destroy', [$country, $state]))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(State::find($state->id))->toBeNull();
});

test('a state referenced by a tax zone cannot be removed', function () {
    $country = Country::factory()->create();
    $state = State::factory()->create(['country_id' => $country->id]);

    TaxZoneState::create([
        'tax_zone_id' => TaxZone::factory()->create()->id,
        'state_id' => $state->id,
    ]);

    $this->delete(route('panel.settings.countries.states.destroy', [$country, $state]))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(State::find($state->id))->not->toBeNull();
});

test('a state belonging to another country cannot be removed through this country', function () {
    $country = Country::factory()->create();
    $other = Country::factory()->create();
    $state = State::factory()->create(['country_id' => $other->id]);

    $this->delete(route('panel.settings.countries.states.destroy', [$country, $state]))
        ->assertNotFound();

    expect(State::find($state->id))->not->toBeNull();
});

test('a country with no references can be deleted', function () {
    $country = Country::factory()->create();

    $this->delete(route('panel.settings.countries.destroy', $country))
        ->assertRedirect(route('panel.settings.countries.index'))
        ->assertSessionHas('success');

    expect(Country::find($country->id))->toBeNull();
});

test('a country with states cannot be deleted and shows a flash error', function () {
    $country = Country::factory()->create();
    State::factory()->create(['country_id' => $country->id]);

    $this->from(route('panel.settings.countries.edit', $country))
        ->delete(route('panel.settings.countries.destroy', $country))
        ->assertRedirect(route('panel.settings.countries.edit', $country))
        ->assertSessionHas('error');

    expect(Country::find($country->id))->not->toBeNull();
});
