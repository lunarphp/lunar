<?php

use Lunar\Core\Contracts\CarrierManifest;
use Lunar\Core\Contracts\ShippingCarrier;
use Lunar\Core\Facades\Carriers;
use Lunar\Core\Shipping\GenericCarrier;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class)->group('shipping.carriers');

it('registers carriers from config', function () {
    config()->set('lunar.shipping.carriers', [
        'acme' => [
            'name' => 'ACME Couriers',
            'tracking_url' => 'https://acme.test/track/{tracking_number}',
            'services' => ['Next Day', 'Economy'],
        ],
    ]);

    // Rebind so the manifest re-reads config.
    app()->forgetInstance(CarrierManifest::class);

    $carrier = Carriers::get('acme');

    expect($carrier)->toBeInstanceOf(ShippingCarrier::class)
        ->and($carrier->getName())->toBe('ACME Couriers')
        ->and($carrier->getServices())->toBe(['Next Day', 'Economy']);
});

it('builds a tracking url from the template', function () {
    $carrier = new GenericCarrier(
        key: 'acme',
        name: 'ACME',
        trackingUrl: 'https://acme.test/track/{tracking_number}',
    );

    expect($carrier->getTrackingUrl('AB 12'))->toBe('https://acme.test/track/AB%2012');
});

it('returns null tracking url when no template is set', function () {
    $carrier = new GenericCarrier(key: 'acme', name: 'ACME');

    expect($carrier->getTrackingUrl('ABC123'))->toBeNull();
});

it('validates tracking numbers against an optional pattern', function () {
    $carrier = new GenericCarrier(
        key: 'acme',
        name: 'ACME',
        trackingNumberPattern: '/^[A-Z]{2}\d{6}$/',
    );

    expect($carrier->validateTrackingNumber('AB123456'))->toBeTrue()
        ->and($carrier->validateTrackingNumber('nope'))->toBeFalse();
});

it('always passes validation without a pattern', function () {
    $carrier = new GenericCarrier(key: 'acme', name: 'ACME');

    expect($carrier->validateTrackingNumber('anything'))->toBeTrue();
});

it('registers a custom carrier instance', function () {
    app()->forgetInstance(CarrierManifest::class);

    Carriers::register(new GenericCarrier(key: 'custom', name: 'Custom Carrier'));

    expect(Carriers::get('custom')?->getName())->toBe('Custom Carrier')
        ->and(Carriers::get(null))->toBeNull();
});
