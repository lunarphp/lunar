<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Enums\PaymentTerms;
use Lunar\Core\Exceptions\Carts\CartException;
use Lunar\Core\Facades\ShippingManifest;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartAddress;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\TaxClass;
use Lunar\Core\Validation\Cart\ValidateCartForOrderCreation;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('can validate missing billing address', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $validator = app(ValidateCartForOrderCreation::class)->using(
        cart: $cart
    );

    $this->expectException(CartException::class);
    $this->expectExceptionMessage(__('lunar::exceptions.carts.billing_missing'));

    $validator->validate();
});

test('can validate populated billing address', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $validator = app(ValidateCartForOrderCreation::class)->using(
        cart: $cart
    );

    CartAddress::factory()->create([
        'type' => 'billing',
        'cart_id' => $cart->id,
    ]);

    expect($validator->validate())->toBeTrue();
});

test('can validate partial billing address', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $validator = app(ValidateCartForOrderCreation::class)->using(
        cart: $cart
    );

    CartAddress::factory()->create([
        'type' => 'billing',
        'cart_id' => $cart->id,
        'first_name' => null,
        'line_one' => null,
        'city' => null,
        'postcode' => null,
        'country_id' => null,
    ]);

    try {
        $validator->validate();
    } catch (CartException $e) {
        $errors = $e->errors();

        expect($errors->has([
            'country_id',
            'first_name',
            'line_one',
            'city',
            'postcode',
        ]))->toBeTrue();
    }
});

test('can validate missing shipping option', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->create([
        'shippable' => true,
    ]);

    Price::factory()->create([
        'currency_id' => $currency->id,
        'priceable_id' => $purchasable->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'price' => 500,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    $validator = app(ValidateCartForOrderCreation::class)->using(
        cart: $cart
    );

    CartAddress::factory()->create([
        'type' => 'billing',
        'cart_id' => $cart->id,
    ]);

    expect(fn () => $validator->validate())->toThrow(CartException::class);

});

test('can validate collection with partial shipping address', function () {
    $currency = Currency::factory()->create();
    $taxClass = TaxClass::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->create([
        'shippable' => true,
    ]);

    Price::factory()->create([
        'currency_id' => $currency->id,
        'priceable_id' => $purchasable->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'price' => 500,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    $shippingOption = new ShippingOption(
        name: 'Pickup',
        description: 'Pickup',
        identifier: 'PICKUP',
        price: new PriceValue(0, $cart->currency),
        taxClass: $taxClass,
        pickupLocation: Location::factory()->create(),
    );

    ShippingManifest::addOption($shippingOption);

    CartAddress::factory()->create([
        'type' => 'shipping',
        'cart_id' => $cart->id,
        'first_name' => null,
        'line_one' => null,
        'city' => null,
        'postcode' => null,
        'country_id' => null,
        'shipping_option' => $shippingOption->getIdentifier(),
    ]);

    CartAddress::factory()->create([
        'type' => 'billing',
        'cart_id' => $cart->id,
    ]);

    $validator = app(ValidateCartForOrderCreation::class)->using(
        cart: $cart
    );

    expect($validator->validate())->toBeTrue();
});

test('can validate delivery with partial shipping address', function () {
    $currency = Currency::factory()->create();
    $taxClass = TaxClass::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->create([
        'shippable' => true,
    ]);

    Price::factory()->create([
        'currency_id' => $currency->id,
        'priceable_id' => $purchasable->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'price' => 500,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    $shippingOption = new ShippingOption(
        name: 'Basic Delivery',
        description: 'Basic Delivery',
        identifier: 'BASDEL',
        price: new PriceValue(500, $cart->currency),
        taxClass: $taxClass
    );

    ShippingManifest::addOption($shippingOption);

    CartAddress::factory()->create([
        'type' => 'shipping',
        'cart_id' => $cart->id,
        'first_name' => null,
        'line_one' => null,
        'city' => null,
        'postcode' => null,
        'country_id' => null,
        'shipping_option' => $shippingOption->getIdentifier(),
    ]);

    CartAddress::factory()->create([
        'type' => 'billing',
        'cart_id' => $cart->id,
    ]);

    $validator = app(ValidateCartForOrderCreation::class)->using(
        cart: $cart
    );

    try {
        $validator->validate();
    } catch (CartException $e) {
        $errors = $e->errors();

        expect($errors->has([
            'country_id',
            'first_name',
            'line_one',
            'city',
            'postcode',
        ]))->toBeTrue();
    }
});

test('fails when a cart line points at a deleted purchasable', function () {
    $currency = Currency::factory()->create();
    $taxClass = TaxClass::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->create([
        'shippable' => false,
    ]);

    Price::factory()->create([
        'currency_id' => $currency->id,
        'priceable_id' => $purchasable->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'price' => 500,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    CartAddress::factory()->create([
        'type' => 'billing',
        'cart_id' => $cart->id,
    ]);

    $purchasable->delete();

    $line = $cart->lines()->first();

    $validator = app(ValidateCartForOrderCreation::class)->using(
        cart: $cart->fresh()
    );

    $this->expectException(CartException::class);
    $this->expectExceptionMessage(__('lunar::exceptions.carts.line_unavailable', [
        'identifier' => "#{$line->id}",
    ]));

    $validator->validate();
});

test('fails when a cart line points at a draft product', function () {
    $currency = Currency::factory()->create();
    $taxClass = TaxClass::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $product = Product::factory()->create(['status' => 'published']);
    $purchasable = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'shippable' => false,
    ]);

    Price::factory()->create([
        'currency_id' => $currency->id,
        'priceable_id' => $purchasable->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'price' => 500,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    CartAddress::factory()->create([
        'type' => 'billing',
        'cart_id' => $cart->id,
    ]);

    $product->update(['status' => 'draft']);

    $validator = app(ValidateCartForOrderCreation::class)->using(
        cart: $cart->fresh()
    );

    $this->expectException(CartException::class);
    $this->expectExceptionMessage(__('lunar::exceptions.carts.line_unavailable', [
        'identifier' => $purchasable->getIdentifier(),
    ]));

    $validator->validate();
});

test('can validate delivery with populated shipping address', function () {
    $currency = Currency::factory()->create();
    $taxClass = TaxClass::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->create([
        'shippable' => true,
    ]);

    Price::factory()->create([
        'currency_id' => $currency->id,
        'priceable_id' => $purchasable->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'price' => 500,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    $shippingOption = new ShippingOption(
        name: 'Basic Delivery',
        description: 'Basic Delivery',
        identifier: 'BASDEL',
        price: new PriceValue(500, $cart->currency),
        taxClass: $taxClass
    );

    ShippingManifest::addOption($shippingOption);

    CartAddress::factory()->create([
        'type' => 'shipping',
        'cart_id' => $cart->id,
        'shipping_option' => $shippingOption->getIdentifier(),
    ]);

    CartAddress::factory()->create([
        'type' => 'billing',
        'cart_id' => $cart->id,
    ]);

    $validator = app(ValidateCartForOrderCreation::class)->using(
        cart: $cart
    );

    expect($validator->validate())->toBeTrue();
});

function cartWithShippingOption(bool $pickup): Cart
{
    $currency = Currency::factory()->create();
    $taxClass = TaxClass::factory()->create();

    $cart = Cart::factory()->create(['currency_id' => $currency->id]);

    $purchasable = ProductVariant::factory()->create(['shippable' => true]);

    Price::factory()->create([
        'currency_id' => $currency->id,
        'priceable_id' => $purchasable->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'price' => 500,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    $shippingOption = new ShippingOption(
        name: 'Option',
        description: 'Option',
        identifier: $pickup ? 'PICKUP' : 'DELIVERY',
        price: new PriceValue(0, $cart->currency),
        taxClass: $taxClass,
        pickupLocation: $pickup ? Location::factory()->create() : null,
    );

    ShippingManifest::addOption($shippingOption);

    CartAddress::factory()->create([
        'type' => 'shipping',
        'cart_id' => $cart->id,
        'shipping_option' => $shippingOption->getIdentifier(),
    ]);

    CartAddress::factory()->create([
        'type' => 'billing',
        'cart_id' => $cart->id,
    ]);

    return $cart->refresh();
}

test('can validate pickup terms with a pickup shipping option', function () {
    $cart = cartWithShippingOption(pickup: true);
    $cart->setPaymentTerms(PaymentTerms::OnPickup);

    expect(app(ValidateCartForOrderCreation::class)->using(cart: $cart)->validate())->toBeTrue();
});

test('rejects pickup terms with a delivery shipping option', function () {
    $cart = cartWithShippingOption(pickup: false);
    $cart->setPaymentTerms(PaymentTerms::OnPickup);

    app(ValidateCartForOrderCreation::class)->using(cart: $cart)->validate();
})->throws(CartException::class, 'Pay on pickup needs at least one item to pick up');

test('rejects pickup terms on a cart with nothing to ship', function () {
    $cart = Cart::factory()->create(['currency_id' => Currency::factory()->create()->id]);
    CartAddress::factory()->create(['type' => 'billing', 'cart_id' => $cart->id]);
    $cart->setPaymentTerms(PaymentTerms::OnPickup);

    app(ValidateCartForOrderCreation::class)->using(cart: $cart->refresh())->validate();
})->throws(CartException::class, 'Pay on pickup needs at least one item to pick up');

test('allows on account terms with a delivery shipping option', function () {
    $cart = cartWithShippingOption(pickup: false);
    $cart->setPaymentTerms(PaymentTerms::OnAccount);

    expect(app(ValidateCartForOrderCreation::class)->using(cart: $cart)->validate())->toBeTrue();
});

test('can validate delivery terms with a delivery shipping option', function () {
    $cart = cartWithShippingOption(pickup: false);
    $cart->setPaymentTerms(PaymentTerms::OnDelivery);

    expect(app(ValidateCartForOrderCreation::class)->using(cart: $cart)->validate())->toBeTrue();
});

test('rejects delivery terms with a pickup shipping option', function () {
    $cart = cartWithShippingOption(pickup: true);
    $cart->setPaymentTerms(PaymentTerms::OnDelivery);

    app(ValidateCartForOrderCreation::class)->using(cart: $cart)->validate();
})->throws(CartException::class, 'Pay on delivery needs at least one item to deliver');

test('rejects delivery terms on a cart with nothing to ship', function () {
    $cart = Cart::factory()->create(['currency_id' => Currency::factory()->create()->id]);
    CartAddress::factory()->create(['type' => 'billing', 'cart_id' => $cart->id]);
    $cart->setPaymentTerms(PaymentTerms::OnDelivery);

    app(ValidateCartForOrderCreation::class)->using(cart: $cart->refresh())->validate();
})->throws(CartException::class, 'Pay on delivery needs at least one item to deliver');

test('a cart with every line picked up needs no shipping option or address', function () {
    $currency = Currency::factory()->create();
    $cart = Cart::factory()->create(['currency_id' => $currency->id]);
    $purchasable = ProductVariant::factory()->create(['shippable' => true]);
    Price::factory()->create([
        'currency_id' => $currency->id,
        'priceable_id' => $purchasable->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'price' => 500,
    ]);
    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
        'fulfilment_method' => 'pickup',
    ]);
    CartAddress::factory()->create(['type' => 'billing', 'cart_id' => $cart->id]);

    expect(app(ValidateCartForOrderCreation::class)->using(cart: $cart->refresh())->validate())->toBeTrue();
});

test('rejects a pickup option when a line is set to be delivered', function () {
    $cart = cartWithShippingOption(pickup: true);
    $cart->lines->first()->update(['fulfilment_method' => 'shipping']);

    app(ValidateCartForOrderCreation::class)->using(cart: $cart->refresh())->validate();
})->throws(CartException::class, 'Items set for delivery need a delivery shipping option');

test('allows pickup terms on a delivered cart with a line set to pickup', function () {
    $cart = cartWithShippingOption(pickup: false);
    $line = $cart->lines->first();
    $cart->lines()->create([
        'purchasable_type' => $line->purchasable_type,
        'purchasable_id' => $line->purchasable_id,
        'quantity' => 1,
        'fulfilment_method' => 'pickup',
    ]);
    $cart->setPaymentTerms(PaymentTerms::OnPickup);

    expect(app(ValidateCartForOrderCreation::class)->using(cart: $cart->refresh()->load('lines.purchasable'))->validate())->toBeTrue();
});
