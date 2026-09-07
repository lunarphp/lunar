<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Lunar\Checkout\Contracts\Actions\SetsFulfilment;
use Lunar\Checkout\DataTypes\PickupPoint;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Facades\ShippingManifest;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\TaxClass;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;
use Lunar\Tests\Checkout\Utils\PickupPointsStub;

uses(TestCase::class, RefreshDatabase::class);

afterEach(fn () => PickupPointsStub::unbind());

/**
 * A deliverable cart that also has a collect option on the manifest, with
 * `standard` stored, so a test can move between modes.
 */
function fulfilmentCart(): Cart
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

    return $cart->refresh();
}

it('records collect mode without an option when the cart has no shipping address', function () {
    $cart = fulfilmentCart();
    $cart->addresses()->where('type', 'shipping')->delete();
    $cart = $cart->refresh();

    $cart = app(SetsFulfilment::class)->execute($cart, 'collect');

    expect($cart->meta['fulfilment'])->toBe('collect')
        ->and($cart->shippingAddress)->toBeNull();
});

it('stores the collect option when a shipping address exists', function () {
    $cart = app(SetsFulfilment::class)->execute(fulfilmentCart(), 'collect');

    expect($cart->shippingAddress->shipping_option)->toBe('collection');
});

it('auto-selects a single offered point', function () {
    PickupPointsStub::bind([new PickupPoint('dartford', 'Dartford', ['DA2 6EP'])]);

    $cart = app(SetsFulfilment::class)->execute(fulfilmentCart(), 'collect');

    expect($cart->meta['pickup_point']['handle'])->toBe('dartford');
});

it('leaves several offered points unchosen until one is named', function () {
    PickupPointsStub::bind([new PickupPoint('london', 'London'), new PickupPoint('dartford', 'Dartford')]);

    $cart = app(SetsFulfilment::class)->execute(fulfilmentCart(), 'collect');
    expect($cart->meta['pickup_point'] ?? null)->toBeNull();

    $cart = app(SetsFulfilment::class)->execute($cart, 'collect', 'dartford');
    expect($cart->meta['pickup_point']['name'])->toBe('Dartford');
});

it('rejects a handle the provider does not offer', function () {
    PickupPointsStub::bind([new PickupPoint('london', 'London')]);

    app(SetsFulfilment::class)->execute(fulfilmentCart(), 'collect', 'mars');
})->throws(ValidationException::class);

it('rejects a mode that is neither delivery nor collect', function () {
    app(SetsFulfilment::class)->execute(fulfilmentCart(), 'teleport');
})->throws(ValidationException::class);

it('returns to delivery: forgets the point and hands the option to the first courier', function () {
    PickupPointsStub::bind([new PickupPoint('dartford', 'Dartford')]);
    $cart = app(SetsFulfilment::class)->execute(fulfilmentCart(), 'collect');
    expect($cart->shippingAddress->shipping_option)->toBe('collection');

    $cart = app(SetsFulfilment::class)->execute($cart, 'delivery');

    expect($cart->meta['fulfilment'])->toBe('delivery')
        ->and($cart->meta['pickup_point'] ?? null)->toBeNull()
        ->and($cart->shippingAddress->shipping_option)->toBe('standard');
});

it('nulls the stored option when delivery is chosen and no courier option exists', function () {
    $cart = CheckoutCart::orderable(collect: true);
    $cart = app(SetsFulfilment::class)->execute($cart, 'collect');
    expect($cart->shippingAddress->shipping_option)->toBe('collection');

    $cart = app(SetsFulfilment::class)->execute($cart, 'delivery');

    expect($cart->shippingAddress->shipping_option)->toBeNull();
});

it('merges into existing cart meta rather than replacing it', function () {
    $cart = fulfilmentCart();
    $cart->meta = ['gift_note' => 'Happy birthday'];
    $cart->save();

    $cart = app(SetsFulfilment::class)->execute($cart->refresh(), 'collect');

    expect($cart->meta['gift_note'])->toBe('Happy birthday');
});
