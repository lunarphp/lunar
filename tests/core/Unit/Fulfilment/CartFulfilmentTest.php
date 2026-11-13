<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Carts\MergeCart;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Drivers\FulfilmentMethods\Pickup;
use Lunar\Core\Drivers\FulfilmentMethods\Shipping;
use Lunar\Core\Exceptions\Carts\CartException;
use Lunar\Core\Facades\ShippingManifest;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartAddress;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\TaxClass;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->currency = Currency::factory()->create(['default' => true]);
    $this->defaultLocation = Location::factory()->create(['default' => true]);
    $this->cart = Cart::factory()->create(['currency_id' => $this->currency->id]);
});

function pricedVariant(Currency $currency, array $attributes = []): ProductVariant
{
    $variant = ProductVariant::factory()->inStock(100)->create($attributes);

    Price::factory()->create([
        'price' => 100,
        'currency_id' => $currency->id,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
    ]);

    return $variant;
}

function chooseShippingOption(Cart $cart, ?Location $pickupLocation = null): ShippingOption
{
    $option = new ShippingOption(
        name: 'Option',
        description: 'Option',
        identifier: $pickupLocation ? 'PICKUP' : 'DELIVERY',
        price: new PriceValue(0, $cart->currency),
        taxClass: TaxClass::factory()->create(),
        pickupLocation: $pickupLocation,
    );

    ShippingManifest::addOption($option);

    CartAddress::factory()->create([
        'type' => 'shipping',
        'cart_id' => $cart->id,
        'shipping_option' => $option->getIdentifier(),
    ]);

    return $option;
}

test('a cart has no fulfilment method until one is chosen', function () {
    $this->cart->add(pricedVariant($this->currency));

    expect($this->cart->fulfilmentMethod())->toBeNull()
        ->and($this->cart->lines->first()->fulfilmentMethod())->toBeNull()
        ->and($this->cart->resolvedPickupLocation())->toBeNull();
});

test('the cart method is read from the chosen shipping option', function () {
    $this->cart->add(pricedVariant($this->currency));
    chooseShippingOption($this->cart, pickupLocation: $this->defaultLocation);

    $cart = $this->cart->refresh();

    expect($cart->fulfilmentMethod()->getKey())->toBe(Pickup::KEY)
        ->and($cart->lines->first()->fulfilmentMethod()->getKey())->toBe(Pickup::KEY);
});

test('a delivery shipping option resolves to shipping', function () {
    $this->cart->add(pricedVariant($this->currency));
    chooseShippingOption($this->cart);

    expect($this->cart->refresh()->fulfilmentMethod()->getKey())->toBe(Shipping::KEY);
});

test('the explicit cart method wins over the shipping option', function () {
    $this->cart->add(pricedVariant($this->currency));
    chooseShippingOption($this->cart);

    $cart = $this->cart->refresh()->setFulfilmentMethod(Pickup::KEY);

    expect($cart->fulfilmentMethod()->getKey())->toBe(Pickup::KEY)
        ->and($cart->lines->first()->fulfilmentMethod()->getKey())->toBe(Pickup::KEY);
});

test('a line override wins over the cart default', function () {
    $this->cart->add(pricedVariant($this->currency), fulfilmentMethod: Pickup::KEY);
    $this->cart->add(pricedVariant($this->currency));

    $cart = $this->cart->setFulfilmentMethod(Shipping::KEY);

    expect($cart->lines->map(fn ($line) => $line->fulfilmentMethod()?->getKey())->all())
        ->toBe([Pickup::KEY, Shipping::KEY]);
});

test('a cart default the line cannot use is skipped, not forced', function () {
    $this->cart->add(pricedVariant($this->currency, ['shippable' => false]));

    $cart = $this->cart->setFulfilmentMethod(Shipping::KEY);

    expect($cart->lines->first()->fulfilmentMethod())->toBeNull();
});

test('a restricted item keeps a method it allows when the cart default does not', function () {
    $this->cart->add(pricedVariant($this->currency, ['fulfilment_methods' => [Pickup::KEY]]));
    $this->cart->add(pricedVariant($this->currency));

    $cart = $this->cart->setFulfilmentMethod(Shipping::KEY);

    expect($cart->lines->map(fn ($line) => $line->fulfilmentMethod()?->getKey())->all())
        ->toBe([Pickup::KEY, Shipping::KEY])
        ->and($cart->resolvedPickupLocation()->id)->toBe($this->defaultLocation->id);
});

test('an unregistered method key falls through to the next step', function () {
    $this->cart->add(pricedVariant($this->currency));
    $this->cart->lines()->update(['fulfilment_method' => 'gone']);

    expect($this->cart->refresh()->lines->first()->fulfilmentMethod())->toBeNull();
});

test('the pickup location prefers the cart, then the shipping option, then the default', function () {
    $this->cart->add(pricedVariant($this->currency));
    $optionLocation = Location::factory()->create();
    chooseShippingOption($this->cart, pickupLocation: $optionLocation);

    $cart = $this->cart->refresh();

    expect($cart->resolvedPickupLocation()->id)->toBe($optionLocation->id);

    $counter = Location::factory()->create();

    expect($cart->setPickupLocation($counter)->resolvedPickupLocation()->id)->toBe($counter->id)
        ->and($cart->setPickupLocation(null)->resolvedPickupLocation()->id)->toBe($optionLocation->id);
});

test('the same variant for delivery and for pickup is two lines', function () {
    $variant = pricedVariant($this->currency);

    $this->cart->add($variant, 1, fulfilmentMethod: Shipping::KEY);
    $this->cart->add($variant, 1, fulfilmentMethod: Pickup::KEY);
    $this->cart->add($variant, 2, fulfilmentMethod: Pickup::KEY);

    expect($this->cart->refresh()->lines->pluck('quantity', 'fulfilment_method')->all())
        ->toBe([Shipping::KEY => 1, Pickup::KEY => 3]);
});

test('addLines passes the fulfilment method through', function () {
    $cart = $this->cart->addLines([
        ['purchasable' => pricedVariant($this->currency), 'quantity' => 1, 'fulfilment_method' => Pickup::KEY],
    ]);

    expect($cart->lines->first()->fulfilment_method)->toBe(Pickup::KEY);
});

test('adding a line with a method the item does not allow is rejected', function () {
    $this->cart->add(pricedVariant($this->currency, ['fulfilment_methods' => [Pickup::KEY]]), fulfilmentMethod: Shipping::KEY);
})->throws(CartException::class);

test('adding a line with an unknown method is rejected', function () {
    $this->cart->add(pricedVariant($this->currency), fulfilmentMethod: 'teleport');
})->throws(CartException::class, 'The fulfilment method "teleport" is not available');

test('setting an unknown cart method is rejected', function () {
    $this->cart->setFulfilmentMethod('teleport');
})->throws(CartException::class, 'The fulfilment method "teleport" is not available');

test('a line override is validated against the item', function () {
    $this->cart->add(pricedVariant($this->currency, ['fulfilment_methods' => [Pickup::KEY]]));

    $line = $this->cart->refresh()->lines->first();

    expect($line->setFulfilmentMethod(Pickup::KEY)->fulfilment_method)->toBe(Pickup::KEY)
        ->and($line->setFulfilmentMethod(null)->fulfilment_method)->toBeNull();

    $line->setFulfilmentMethod(Shipping::KEY);
})->throws(CartException::class);

test('changing the fulfilment choice changes the fingerprint', function () {
    $this->cart->add(pricedVariant($this->currency));
    $cart = $this->cart->refresh();
    $before = $cart->fingerprint();

    $cart->lines->first()->setFulfilmentMethod(Pickup::KEY);
    $afterLine = $cart->refresh()->fingerprint();

    $afterCart = $cart->setFulfilmentMethod(Shipping::KEY)->fingerprint();
    $afterLocation = $cart->setPickupLocation($this->defaultLocation)->fingerprint();

    expect(collect([$before, $afterLine, $afterCart, $afterLocation])->unique())->toHaveCount(4);
});

test('merging carts keeps lines with different methods apart', function () {
    $variant = pricedVariant($this->currency);
    $source = Cart::factory()->create(['currency_id' => $this->currency->id]);

    $this->cart->add($variant, 1, fulfilmentMethod: Shipping::KEY);
    $source->add($variant, 2, fulfilmentMethod: Pickup::KEY);

    app(MergeCart::class)->execute($this->cart->refresh(), $source->refresh());

    expect($this->cart->refresh()->lines->pluck('quantity', 'fulfilment_method')->all())
        ->toBe([Shipping::KEY => 1, Pickup::KEY => 2]);
});
