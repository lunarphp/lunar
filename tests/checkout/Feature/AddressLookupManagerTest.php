<?php

use Lunar\Checkout\AddressLookup\NullLookup;
use Lunar\Checkout\Contracts\AddressLookup;
use Lunar\Checkout\Managers\AddressLookupManager;
use Lunar\Tests\Checkout\TestCase;

uses(TestCase::class);

it('resolves the null driver by default', function () {
    expect(app(AddressLookup::class))->toBeInstanceOf(NullLookup::class);
});

it('falls back to the null driver when the configured value is null', function () {
    // `CHECKOUT_ADDRESS_LOOKUP_DRIVER=null` in a host's .env resolves to a real
    // PHP null, not the string "null", so the config default never applies. The
    // manager has to absorb that, or getDefaultDriver() TypeErrors and every
    // route in the checkout group 500s on a fresh setup.
    config()->set('lunar.checkout.address_lookup.driver', null);

    expect(app(AddressLookup::class))->toBeInstanceOf(NullLookup::class);
});

it('falls back to the null driver when the configured value is empty', function () {
    config()->set('lunar.checkout.address_lookup.driver', '');

    expect(app(AddressLookup::class))->toBeInstanceOf(NullLookup::class);
});

it('reports the null driver as unavailable and returns no addresses', function () {
    $driver = app(AddressLookup::class);

    expect($driver->isAvailable())->toBeFalse()
        ->and($driver->lookup('SW1A 1AA'))->toBe([]);
});

it('lets a host register its own driver by name', function () {
    config()->set('lunar.checkout.address_lookup.driver', 'acme');

    app(AddressLookupManager::class)->extend('acme', fn (): AddressLookup => new class implements AddressLookup
    {
        public function lookup(string $postcode): array
        {
            return [];
        }

        public function isAvailable(): bool
        {
            return true;
        }
    });

    expect(app(AddressLookup::class)->isAvailable())->toBeTrue();
});

it('throws when the configured driver does not exist', function () {
    config()->set('lunar.checkout.address_lookup.driver', 'nope');

    app(AddressLookup::class);
})->throws(InvalidArgumentException::class);
