<?php

uses(\Lunar\Tests\Shipping\TestCase::class);

use Lunar\Shipping\Models\ShippingZone;
use Lunar\Shipping\Models\ShippingZonePostcode;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can create model', function () {
    $shippingZone = ShippingZone::factory()->create();

    ShippingZonePostcode::create([
        'shipping_zone_id' => $shippingZone->id,
        'postcode' => 'AB1 2BA',
    ]);

    $this->assertDatabaseHas((new ShippingZonePostcode())->getTable(), [
        'shipping_zone_id' => $shippingZone->id,
        'postcode' => 'AB12BA',
    ]);
});

test('can fetch shipping zone relationship', function () {
    $shippingZone = ShippingZone::factory()->create();

    $postcode = ShippingZonePostcode::create([
        'shipping_zone_id' => $shippingZone->id,
        'postcode' => 'AB1 2BA',
    ]);

    expect($postcode->shippingZone)->toBeInstanceOf(ShippingZone::class);
    expect($postcode->shippingZone->id)->toEqual($shippingZone->id);
});

test('postcode is sanitised on save', function () {
    $shippingZone = ShippingZone::factory()->create();

    $postcode = ShippingZonePostcode::create([
        'shipping_zone_id' => $shippingZone->id,
        'postcode' => 'AB1 2BA',
    ]);

    expect($postcode->postcode)->toEqual('AB12BA');

    $postcodeTests = [
        '          A B 1 2     B A',
        'AB 12 BA',
        'AB1       2BA ',
    ];

    foreach ($postcodeTests as $ptest) {
        $postcode->update([
            'postcode' => $ptest,
        ]);
        expect($postcode->refresh()->postcode)->toEqual('AB12BA');
    }
});
