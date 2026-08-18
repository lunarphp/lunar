<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Staff;
use Lunar\Core\Models\StockLevel;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');
});

test('the locations index renders with stock counts', function () {
    $location = Location::factory()->create(['name' => 'Main warehouse', 'default' => true]);
    Location::factory()->create(['name' => 'Overflow', 'default' => false]);

    // Stock level creation needs a variant, which needs a default language for URLs.
    Language::factory()->create(['default' => true]);
    StockLevel::create([
        'location_id' => $location->id,
        'product_variant_id' => ProductVariant::factory()->create()->id,
        'on_hand' => 5,
    ]);

    $this->get(route('panel.settings.locations.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/locations/Index')
            ->has('locations.data', 2)
            ->where('locations.data.0.name', 'Main warehouse')
            ->where('locations.data.0.stocked_variants_count', 1)
            ->where('locations.data.1.stocked_variants_count', 0)
            ->has('urls.store')
        );
});

test('a location can be created with an auto-generated handle', function () {
    $this->post(route('panel.settings.locations.store'), [
        'name' => 'Main Warehouse',
    ])->assertRedirect(route('panel.settings.locations.index'))
        ->assertSessionHas('success');

    $location = Location::where('handle', 'main-warehouse')->first();

    expect($location)->not->toBeNull();
    expect($location->default)->toBeFalse();
});

test('a colliding auto-generated handle is rejected as a validation error', function () {
    Location::factory()->create(['name' => 'Main Warehouse', 'handle' => 'main-warehouse']);

    $this->post(route('panel.settings.locations.store'), [
        'name' => 'Main Warehouse',
    ])->assertSessionHasErrors('handle');

    expect(Location::count())->toBe(1);
});

test('creating a second location as default un-defaults the first', function () {
    $first = Location::factory()->create(['default' => true]);

    $this->post(route('panel.settings.locations.store'), [
        'name' => 'Overflow',
        'default' => true,
    ])->assertRedirect(route('panel.settings.locations.index'));

    expect($first->fresh()->default)->toBeFalse();
    expect(Location::where('handle', 'overflow')->first()->default)->toBeTrue();
    expect(Location::where('default', true)->count())->toBe(1);
});

test('a location can be updated', function () {
    $location = Location::factory()->create(['name' => 'Main', 'default' => false]);

    $this->put(route('panel.settings.locations.update', $location), [
        'name' => 'Main warehouse',
        'handle' => 'main-warehouse',
    ])->assertRedirect(route('panel.settings.locations.index'))
        ->assertSessionHas('success');

    expect($location->fresh()->name)->toBe('Main warehouse');
});

test('unsetting default on the default location is rejected with a flash error', function () {
    $location = Location::factory()->create(['default' => true]);

    $this->from(route('panel.settings.locations.edit', $location))
        ->put(route('panel.settings.locations.update', $location), [
            'name' => $location->name,
            'handle' => $location->handle,
            'default' => false,
        ])->assertRedirect(route('panel.settings.locations.edit', $location))
        ->assertSessionHas('error', __('panel::locations.default_unset_blocked'));

    expect($location->fresh()->default)->toBeTrue();
});

test('the default location cannot be deleted and shows a flash error', function () {
    $location = Location::factory()->create(['default' => true]);

    $this->from(route('panel.settings.locations.edit', $location))
        ->delete(route('panel.settings.locations.destroy', $location))
        ->assertRedirect(route('panel.settings.locations.edit', $location))
        ->assertSessionHas('error', __('panel::locations.delete_blocked_default'));

    expect(Location::find($location->id))->not->toBeNull();
});

test('a location with stock cannot be deleted and shows a flash error', function () {
    $location = Location::factory()->create(['default' => false]);

    Language::factory()->create(['default' => true]);
    StockLevel::create([
        'location_id' => $location->id,
        'product_variant_id' => ProductVariant::factory()->create()->id,
        'on_hand' => 5,
    ]);

    $this->from(route('panel.settings.locations.edit', $location))
        ->delete(route('panel.settings.locations.destroy', $location))
        ->assertRedirect(route('panel.settings.locations.edit', $location))
        ->assertSessionHas('error', __('panel::locations.delete_blocked'));

    expect(Location::find($location->id))->not->toBeNull();
});

test('an unused location can be deleted', function () {
    $location = Location::factory()->create(['default' => false]);

    $this->delete(route('panel.settings.locations.destroy', $location))
        ->assertRedirect(route('panel.settings.locations.index'))
        ->assertSessionHas('success');

    expect(Location::find($location->id))->toBeNull();
});
