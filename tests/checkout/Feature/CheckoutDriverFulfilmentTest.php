<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Checkout\DataTypes\PickupPoint;
use Lunar\Checkout\Events\FulfilmentSet;
use Lunar\Checkout\Events\PickupPointSet;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Facades\CartSession;
use Lunar\Core\Facades\ShippingManifest;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\TaxClass;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;
use Lunar\Tests\Checkout\Utils\PickupPointsStub;

uses(TestCase::class, RefreshDatabase::class);

afterEach(fn () => PickupPointsStub::unbind());

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
    Event::fake([FulfilmentSet::class, PickupPointSet::class]);
    PickupPointsStub::bind([new PickupPoint('london', 'London', ['SE20 8RA']), new PickupPoint('dartford', 'Dartford')]);

    $cart = driverCart();
    $driver = app(CheckoutDriver::class);
    $session = $driver->resolveOrCreateSession($cart);

    $driver->setFulfilment($session, 'collect');
    expect($driver->getFulfilment($session))->toBe('collect')
        ->and($driver->getSelectedShippingOption($session))->toBe('collection')
        ->and($driver->getSelectedPickupPoint($session))->toBeNull()
        ->and($driver->getPickupPoints($session))->toBe([
            ['id' => 'london', 'name' => 'London', 'lines' => ['SE20 8RA']],
            ['id' => 'dartford', 'name' => 'Dartford', 'lines' => []],
        ]);

    $driver->setPickupPoint($session, 'dartford');
    expect($driver->getSelectedPickupPoint($session))->toBe('dartford');

    Event::assertDispatched(FulfilmentSet::class, fn (FulfilmentSet $e): bool => $e->mode === 'collect');
    Event::assertDispatched(PickupPointSet::class, fn (PickupPointSet $e): bool => $e->handle === 'dartford');
});

it('flips the mode when a courier option is stored directly while collecting', function () {
    PickupPointsStub::bind([new PickupPoint('dartford', 'Dartford')]);
    $cart = driverCart();
    $driver = app(CheckoutDriver::class);
    $session = $driver->resolveOrCreateSession($cart);

    $driver->setFulfilment($session, 'collect');
    expect($driver->getSelectedPickupPoint($session))->toBe('dartford');

    $driver->setShippingOption($session, 'standard');

    expect($driver->getFulfilment($session))->toBe('delivery')
        ->and($driver->getSelectedPickupPoint($session))->toBeNull();
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

it('mirrors a billing address onto the shipping row in collect mode and stores the option', function () {
    PickupPointsStub::bind([new PickupPoint('dartford', 'Dartford', ['DA2 6EP'])]);

    $cart = driverCart();
    $cart->addresses()->delete();
    $cart = $cart->refresh();
    $driver = app(CheckoutDriver::class);
    $session = $driver->resolveOrCreateSession($cart);

    $driver->setFulfilment($session, 'collect');
    expect($driver->getSelectedShippingOption($session))->toBeNull();

    $driver->storeBillingAddress($session, [
        'first_name' => 'Terry', 'last_name' => 'Sparks', 'line1' => '4 Wallet Road',
        'city' => 'London', 'postcode' => 'SE1 1AA', 'country_code' => 'GB',
    ]);

    $cart = $cart->refresh();

    expect($cart->shippingAddress)->not->toBeNull()
        ->and($cart->shippingAddress->line_one)->toBe('4 Wallet Road')
        ->and($driver->getSelectedShippingOption($session))->toBe('collection')
        ->and($driver->getFulfilment($session))->toBe('collect')
        ->and($driver->getSelectedPickupPoint($session))->toBe('dartford')
        ->and($cart->canCreateOrder())->toBeTrue();
});

it('leaves the shipping row alone when a billing address arrives in delivery mode', function () {
    $cart = driverCart();
    $cart->addresses()->delete();
    $cart = $cart->refresh();
    $driver = app(CheckoutDriver::class);
    $session = $driver->resolveOrCreateSession($cart);

    $driver->storeBillingAddress($session, [
        'first_name' => 'Terry', 'last_name' => 'Sparks', 'line1' => '4 Wallet Road',
        'city' => 'London', 'postcode' => 'SE1 1AA', 'country_code' => 'GB',
    ]);

    expect($cart->refresh()->shippingAddress)->toBeNull();
});
