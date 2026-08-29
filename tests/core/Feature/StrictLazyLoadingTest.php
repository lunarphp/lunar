<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\DataObjects\PriceValue as PriceDataType;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\DiscountTypes\PercentageOff;
use Lunar\Core\Facades\ShippingManifest;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartAddress;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\TaxClass;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Model::preventLazyLoading();
});

afterEach(function () {
    Model::preventLazyLoading(false);
});

/*
 * Laravel only sets the per-instance preventsLazyLoading flag on models
 * hydrated as part of a multi-row collection (see Builder::hydrate). The
 * tests below load fixtures via Cart::query()->get() / similar so the
 * collection-row flag is in effect — matching how the admin panel hits
 * the violation in real usage.
 */

it('has lazy loading prevention enabled', function () {
    expect(Model::preventsLazyLoading())->toBeTrue();
});

it('calculates carts loaded as a collection without lazy-loading lines, currency or purchasable', function () {
    seedCurrencyAndChannel();

    $cartIds = collect(range(1, 3))->map(fn () => makeCartWithLine())->pluck('id');

    $carts = Cart::query()->whereIn('id', $cartIds)->get();

    expect($carts)->toHaveCount(3);

    foreach ($carts as $cart) {
        $cart->calculate();
        expect($cart->total->value)->toBeGreaterThan(0);
    }
});

it('creates orders from carts loaded as a collection without lazy-loading', function () {
    seedCurrencyAndChannel();
    $taxClass = TaxClass::factory()->create();

    $cartIds = collect(range(1, 3))->map(function () {
        $cart = makeCartWithLine();
        CartAddress::factory()->create(['type' => 'billing', 'cart_id' => $cart->id]);
        CartAddress::factory()->create(['type' => 'shipping', 'cart_id' => $cart->id]);

        return $cart->id;
    });

    $option = new ShippingOption(
        name: 'Strict shipping',
        description: 'Strict shipping',
        identifier: 'STRICT',
        price: new PriceDataType(500, Currency::getDefault(), 1),
        taxClass: $taxClass,
    );
    ShippingManifest::addOption($option);

    $carts = Cart::query()->whereIn('id', $cartIds)->get();

    foreach ($carts as $cart) {
        $cart->setShippingOption($option);
        $cart->calculate();
        $order = $cart->createOrder();

        expect($order)->toBeInstanceOf(Order::class);
    }
});

it('evaluates a coupon discount across a collection of carts without lazy-loading customers or currency', function () {
    seedCurrencyAndChannel();

    $cartIds = collect(range(1, 3))->map(function () {
        $cart = makeCartWithLine();
        $cart->update(['coupon_code' => 'STRICT10']);

        return $cart->id;
    });

    Discount::factory()->create([
        'type' => PercentageOff::class,
        'name' => 'Strict coupon',
        'coupon' => 'STRICT10',
        'starts_at' => now()->subDay(),
        'data' => [
            'percentage' => 10,
        ],
    ]);

    $carts = Cart::query()->whereIn('id', $cartIds)->get();

    foreach ($carts as $cart) {
        $cart->calculate();
        expect($cart->discountTotal->value)->toBeGreaterThan(0);
    }
});

function seedCurrencyAndChannel(): void
{
    if (Currency::query()->where('default', true)->doesntExist()) {
        Currency::factory()->create(['default' => true]);
    }

    if (Channel::query()->where('default', true)->doesntExist()) {
        Channel::factory()->create(['default' => true]);
    }

    if (CustomerGroup::query()->where('default', true)->doesntExist()) {
        CustomerGroup::factory()->create(['default' => true]);
    }
}

function makeCartWithLine(): Cart
{
    $currency = Currency::query()->where('default', true)->first();
    $channel = Channel::query()->where('default', true)->first();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
    ]);

    $variant = ProductVariant::factory()->create(['unit_quantity' => 1]);

    Price::factory()->create([
        'price' => 1000,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
        'quantity' => 1,
    ]);

    return $cart;
}
