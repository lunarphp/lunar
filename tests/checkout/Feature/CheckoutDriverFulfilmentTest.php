<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Checkout\DataTypes\CollectionPoint;
use Lunar\Checkout\Events\CollectionPointSet;
use Lunar\Checkout\Events\FulfilmentSet;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Facades\CartSession;
use Lunar\Core\Facades\ShippingManifest;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\TaxClass;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;
use Lunar\Tests\Checkout\Utils\CollectionPointsStub;

uses(TestCase::class, RefreshDatabase::class);

afterEach(fn () => CollectionPointsStub::unbind());

function driverCart(): Cart
{
    $cart = CheckoutCart::orderable();

    ShippingManifest::addOption(new ShippingOption(
        name: 'Click & collect',
        description: 'Collect from a branch',
        identifier: 'collection',
        price: new PriceValue(0, Currency::query()->firstWhere('default', true)),
        taxClass: TaxClass::query()->firstWhere('default', true),
        collect: true,
    ));

    CartSession::use($cart);

    return $cart->refresh();
}

it('sets the mode and the point through the driver and fires events', function () {
    Event::fake([FulfilmentSet::class, CollectionPointSet::class]);
    CollectionPointsStub::bind([new CollectionPoint('london', 'London', ['SE20 8RA']), new CollectionPoint('dartford', 'Dartford')]);

    $cart = driverCart();
    $driver = app(CheckoutDriver::class);
    $session = $driver->resolveOrCreateSession($cart);

    $driver->setFulfilment($session, 'collect');
    expect($driver->getFulfilment($session))->toBe('collect')
        ->and($driver->getSelectedShippingOption($session))->toBe('collection')
        ->and($driver->getSelectedCollectionPoint($session))->toBeNull()
        ->and($driver->getCollectionPoints($session))->toBe([
            ['id' => 'london', 'name' => 'London', 'lines' => ['SE20 8RA']],
            ['id' => 'dartford', 'name' => 'Dartford', 'lines' => []],
        ]);

    $driver->setCollectionPoint($session, 'dartford');
    expect($driver->getSelectedCollectionPoint($session))->toBe('dartford');

    Event::assertDispatched(FulfilmentSet::class, fn (FulfilmentSet $e): bool => $e->mode === 'collect');
    Event::assertDispatched(CollectionPointSet::class, fn (CollectionPointSet $e): bool => $e->handle === 'dartford');
});

it('flips the mode when a courier option is stored directly while collecting', function () {
    CollectionPointsStub::bind([new CollectionPoint('dartford', 'Dartford')]);
    $cart = driverCart();
    $driver = app(CheckoutDriver::class);
    $session = $driver->resolveOrCreateSession($cart);

    $driver->setFulfilment($session, 'collect');
    expect($driver->getSelectedCollectionPoint($session))->toBe('dartford');

    $driver->setShippingOption($session, 'standard');

    expect($driver->getFulfilment($session))->toBe('delivery')
        ->and($driver->getSelectedCollectionPoint($session))->toBeNull();
});

it('flips the mode to collect when the collect option is stored directly', function () {
    $cart = driverCart();
    $driver = app(CheckoutDriver::class);
    $session = $driver->resolveOrCreateSession($cart);

    $driver->setShippingOption($session, 'collection');

    expect($driver->getFulfilment($session))->toBe('collect');
});

it('stores the collect option once the address arrives for a cart that chose collect first', function () {
    $cart = driverCart();
    $cart->addresses()->where('type', 'shipping')->delete();
    $cart = $cart->refresh();
    $driver = app(CheckoutDriver::class);
    $session = $driver->resolveOrCreateSession($cart);

    $driver->setFulfilment($session, 'collect');
    expect($driver->getSelectedShippingOption($session))->toBeNull();

    $driver->storeShippingAddress($session, [
        'first_name' => 'Terry', 'last_name' => 'Sparks', 'line1' => '1 Trade Counter Way',
        'city' => 'London', 'postcode' => 'SE1 1AA', 'country_code' => 'GB',
    ]);

    expect($driver->getSelectedShippingOption($session))->toBe('collection')
        ->and($driver->getFulfilment($session))->toBe('collect');
});
