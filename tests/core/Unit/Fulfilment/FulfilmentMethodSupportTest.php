<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Contracts\Purchasable;
use Lunar\Core\Drivers\FulfilmentMethods\Digital;
use Lunar\Core\Drivers\FulfilmentMethods\Pickup;
use Lunar\Core\Drivers\FulfilmentMethods\Shipping;
use Lunar\Core\Facades\FulfilmentMethods;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->counter = Location::factory()->default()->acceptsPickup()->create();
});

test('the core methods support purchasables by their capability', function () {
    $physical = ProductVariant::factory()->create(['shippable' => true]);
    $nonShippable = ProductVariant::factory()->create(['shippable' => false]);

    expect(FulfilmentMethods::supporting($physical)->keys()->all())->toBe([Pickup::KEY, Shipping::KEY])
        ->and(FulfilmentMethods::supporting($nonShippable)->keys()->all())->toBe([]);
});

test('only shipping delivers to the customer', function () {
    expect((new Shipping)->requiresDelivery())->toBeTrue()
        ->and((new Pickup)->requiresDelivery())->toBeFalse()
        ->and((new Digital)->requiresDelivery())->toBeFalse();
});

test('digital supports a non-shippable purchasable that needs provisioning', function () {
    $licence = Mockery::mock(Purchasable::class, [
        'isShippable' => false,
        'requiresFulfilment' => true,
        'allowedFulfilmentMethods' => null,
    ]);

    expect(FulfilmentMethods::supporting($licence)->keys()->all())->toBe([Digital::KEY]);
});

test('a variant that does not ship needs fulfilling only when it names its methods', function () {
    $download = ProductVariant::factory()->create(['shippable' => false]);
    $licence = ProductVariant::factory()->create(['shippable' => false, 'fulfilment_methods' => [Digital::KEY]]);

    expect($download->requiresFulfilment())->toBeFalse()
        ->and(FulfilmentMethods::supporting($download)->all())->toBe([])
        ->and($licence->requiresFulfilment())->toBeTrue()
        ->and(FulfilmentMethods::supporting($licence)->keys()->all())->toBe([Digital::KEY]);
});

test('a variant restricted to pickup is only supported by pickup', function () {
    $cement = ProductVariant::factory()->create(['shippable' => true, 'fulfilment_methods' => [Pickup::KEY]]);

    expect($cement->allowedFulfilmentMethods())->toBe([Pickup::KEY])
        ->and(FulfilmentMethods::supporting($cement)->keys()->all())->toBe([Pickup::KEY]);
});

test('the allow-list never adds a method the driver does not support', function () {
    $variant = ProductVariant::factory()->create(['shippable' => false, 'fulfilment_methods' => [Shipping::KEY]]);

    expect(FulfilmentMethods::supporting($variant)->all())->toBe([]);
});

test('an unrestricted variant allows any method', function () {
    expect(ProductVariant::factory()->create()->allowedFulfilmentMethods())->toBeNull()
        ->and(ProductVariant::factory()->create(['fulfilment_methods' => []])->allowedFulfilmentMethods())->toBeNull();
});

test('pickup is only available while a location accepts pickup', function () {
    $physical = ProductVariant::factory()->create(['shippable' => true]);

    expect((new Pickup)->isAvailable())->toBeTrue();

    $this->counter->update(['accepts_pickup' => false]);

    expect((new Pickup)->isAvailable())->toBeFalse()
        ->and((new Pickup)->supports($physical))->toBeTrue()
        ->and(FulfilmentMethods::supporting($physical)->keys()->all())->toBe([Shipping::KEY]);
});

test('the default pickup location prefers the default, then the first by name', function () {
    $this->counter->update(['accepts_pickup' => false]);
    Location::factory()->acceptsPickup()->create(['name' => 'Woolwich']);
    $dartford = Location::factory()->acceptsPickup()->create(['name' => 'Dartford']);

    expect(Location::defaultPickup()->id)->toBe($dartford->id);

    $this->counter->update(['accepts_pickup' => true]);

    expect(Location::defaultPickup()->id)->toBe($this->counter->id);
});
