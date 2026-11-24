<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\ProductVariantResource\Pages\ManageVariantShipping;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.product');

it('shows every fulfilment method allowed by default and stores a restriction', function () {
    Language::factory()->create(['default' => true]);
    Currency::factory()->create(['default' => true, 'decimal_places' => 2]);
    Location::factory()->default()->acceptsPickup()->create();

    $variant = ProductVariant::factory()->create([
        'shippable' => true,
        'length_value' => 1,
        'width_value' => 1,
        'height_value' => 1,
    ]);

    $this->asStaff();

    Livewire::test(ManageVariantShipping::class, ['record' => $variant->getRouteKey()])
        ->assertSchemaStateSet(['fulfilment_methods' => ['pickup', 'shipping']])
        ->fillForm(['fulfilment_methods' => ['shipping']])
        ->call('save')
        ->assertHasNoErrors();

    expect($variant->refresh()->fulfilment_methods)->toBe(['shipping']);

    Livewire::test(ManageVariantShipping::class, ['record' => $variant->getRouteKey()])
        ->assertSchemaStateSet(['fulfilment_methods' => ['shipping']])
        ->fillForm(['fulfilment_methods' => []])
        ->call('save')
        ->assertHasNoErrors();

    expect($variant->refresh())
        ->shippable->toBeFalse()
        ->fulfilment_methods->toBeNull();
});

it('makes a variant that does not ship use a non-physical method without mixing kinds', function () {
    Language::factory()->create(['default' => true]);
    Currency::factory()->create(['default' => true, 'decimal_places' => 2]);
    Location::factory()->default()->acceptsPickup()->create();

    $variant = ProductVariant::factory()->create([
        'shippable' => true,
        'length_value' => 1,
        'width_value' => 1,
        'height_value' => 1,
    ]);

    $this->asStaff();

    Livewire::test(ManageVariantShipping::class, ['record' => $variant->getRouteKey()])
        ->assertSchemaStateSet(['fulfilment_methods' => ['pickup', 'shipping']])
        ->fillForm(['fulfilment_methods' => ['pickup', 'shipping', 'digital']])
        ->assertSchemaStateSet(['fulfilment_methods' => ['digital']])
        ->call('save')
        ->assertHasNoErrors();

    expect($variant->refresh())
        ->shippable->toBeFalse()
        ->fulfilment_methods->toBe(['digital'])
        ->requiresFulfilment()->toBeTrue();

    Livewire::test(ManageVariantShipping::class, ['record' => $variant->getRouteKey()])
        ->assertSchemaStateSet(['fulfilment_methods' => ['digital']]);
});

it('keeps a variant unrestricted and warns about pickup it still uses while no location accepts pickup', function () {
    Language::factory()->create(['default' => true]);
    Currency::factory()->create(['default' => true, 'decimal_places' => 2]);
    Location::factory()->default()->create();

    $unrestricted = ProductVariant::factory()->create(['shippable' => true]);
    $collectOnly = ProductVariant::factory()->create(['shippable' => true, 'fulfilment_methods' => ['pickup']]);

    $this->asStaff();

    Livewire::test(ManageVariantShipping::class, ['record' => $unrestricted->getRouteKey()])
        ->assertSchemaStateSet(['fulfilment_methods' => ['shipping']])
        ->assertDontSee(__('lunar-filament::productvariant.form.fulfilment_methods.unavailable', ['methods' => 'Pickup']))
        ->call('save')
        ->assertHasNoErrors();

    // Shipping is every available physical method, so pickup comes back once a location accepts it.
    expect($unrestricted->refresh()->fulfilment_methods)->toBeNull();

    Livewire::test(ManageVariantShipping::class, ['record' => $collectOnly->getRouteKey()])
        ->assertSchemaStateSet(['fulfilment_methods' => ['pickup']])
        ->assertSee(__('lunar-filament::productvariant.form.fulfilment_methods.unavailable', ['methods' => 'Pickup']));
});
