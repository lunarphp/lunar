<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\DataTypes\Price as PriceDataType;
use Lunar\DataTypes\ShippingOption;
use Lunar\Exceptions\Carts\CartException;
use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Tests\Core\TestCase;
use Lunar\Validation\CartLine\CartLineAvailability;
use Spatie\LaravelBlink\BlinkFacade;

uses(TestCase::class)
    ->group('validation.cart_line');
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->channel = Channel::factory()->create(['default' => true]);
    $this->customerGroup = CustomerGroup::factory()->create(['default' => true]);
    $this->currency = Currency::factory()->create(['default' => true]);

    $this->cart = Cart::factory()->create([
        'currency_id' => $this->currency->id,
        'channel_id' => $this->channel->id,
    ]);
});

test('passes when the product is purchasable in the channel and customer group', function () {
    $variant = ProductVariant::factory()->create();

    $validator = (new CartLineAvailability)->using(
        cart: $this->cart,
        purchasable: $variant,
        quantity: 1,
        meta: []
    );

    expect($validator->validate())->toBeTrue();
});

test('fails when the product has no channel pivot row enabled', function () {
    $variant = ProductVariant::factory()->create();
    $variant->product->channels()->detach();

    $validator = (new CartLineAvailability)->using(
        cart: $this->cart,
        purchasable: $variant,
        quantity: 1,
        meta: []
    );

    expect(fn () => $validator->validate())->toThrow(CartException::class);
});

test('fails when the customer-group pivot is flagged not purchasable', function () {
    $variant = ProductVariant::factory()->create();

    $variant->product->customerGroups()->updateExistingPivot($this->customerGroup->id, [
        'purchasable' => false,
    ]);

    $validator = (new CartLineAvailability)->using(
        cart: $this->cart,
        purchasable: $variant,
        quantity: 1,
        meta: []
    );

    expect(fn () => $validator->validate())->toThrow(CartException::class);
});

test('passes when the product is purchasable but not visible (direct-link only)', function () {
    $variant = ProductVariant::factory()->create();

    $variant->product->customerGroups()->updateExistingPivot($this->customerGroup->id, [
        'visible' => false,
        'purchasable' => true,
    ]);

    $validator = (new CartLineAvailability)->using(
        cart: $this->cart,
        purchasable: $variant,
        quantity: 1,
        meta: []
    );

    expect($validator->validate())->toBeTrue();
});

test('fails when the customer-group pivot is disabled', function () {
    $variant = ProductVariant::factory()->create();

    $variant->product->customerGroups()->updateExistingPivot($this->customerGroup->id, [
        'enabled' => false,
    ]);

    $validator = (new CartLineAvailability)->using(
        cart: $this->cart,
        purchasable: $variant,
        quantity: 1,
        meta: []
    );

    expect(fn () => $validator->validate())->toThrow(CartException::class);
});

test('fails when the channel pivot has not started yet', function () {
    $variant = ProductVariant::factory()->create();

    $variant->product->channels()->updateExistingPivot($this->channel->id, [
        'enabled' => true,
        'starts_at' => now()->addDay(),
        'ends_at' => null,
    ]);

    $validator = (new CartLineAvailability)->using(
        cart: $this->cart,
        purchasable: $variant,
        quantity: 1,
        meta: []
    );

    expect(fn () => $validator->validate())->toThrow(CartException::class);
});

test('fails when the channel pivot has ended', function () {
    $variant = ProductVariant::factory()->create();

    $variant->product->channels()->updateExistingPivot($this->channel->id, [
        'enabled' => true,
        'starts_at' => now()->subWeek(),
        'ends_at' => now()->subDay(),
    ]);

    $validator = (new CartLineAvailability)->using(
        cart: $this->cart,
        purchasable: $variant,
        quantity: 1,
        meta: []
    );

    expect(fn () => $validator->validate())->toThrow(CartException::class);
});

test('fails when the parent product is soft-deleted', function () {
    $variant = ProductVariant::factory()->create();

    $variant->product->delete();

    $validator = (new CartLineAvailability)->using(
        cart: $this->cart,
        purchasable: $variant->fresh(),
        quantity: 1,
        meta: []
    );

    expect(fn () => $validator->validate())->toThrow(CartException::class);
});

test('passes for non-variant purchasables such as shipping options', function () {
    $option = new ShippingOption(
        name: 'Standard',
        description: 'Standard',
        identifier: 'STD',
        price: new PriceDataType(500, Currency::factory()->create(), 1),
        taxClass: TaxClass::factory()->create(),
    );

    $validator = (new CartLineAvailability)->using(
        cart: $this->cart,
        purchasable: $option,
        quantity: 1,
        meta: []
    );

    expect($validator->validate())->toBeTrue();
});

test('passes when neither the cart nor the defaults resolve a customer group', function () {
    $this->customerGroup->update(['default' => false]);
    BlinkFacade::flush();

    $variant = ProductVariant::factory()->create();

    $validator = (new CartLineAvailability)->using(
        cart: $this->cart,
        purchasable: $variant,
        quantity: 1,
        meta: []
    );

    expect($validator->validate())->toBeTrue();
});

test('cart add throws when the product is not visible in the channel', function () {
    $variant = ProductVariant::factory()->create();
    $variant->product->channels()->detach();

    expect(fn () => $this->cart->add($variant))
        ->toThrow(CartException::class);
});

test('resolves the purchasable from cartLineId when none is passed directly', function () {
    $variant = ProductVariant::factory()->create();
    $variant->product->channels()->detach();

    $line = $this->cart->lines()->create([
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
        'quantity' => 1,
    ]);

    $validator = (new CartLineAvailability)->using(
        cart: $this->cart->fresh(),
        cartLineId: $line->id,
        quantity: 2,
        meta: []
    );

    expect(fn () => $validator->validate())->toThrow(CartException::class);
});
