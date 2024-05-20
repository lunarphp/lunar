<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Lunar\DiscountTypes\AmountOff;
use Lunar\Facades\CartSession;
use Lunar\Models\Brand;
use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Discount;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Lunar\Tests\Core\Stubs\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Currency::factory()->create([
        'code' => 'GBP',
        'decimal_places' => 2,
    ]);

    Channel::factory()->create([
        'default' => true,
    ]);

    CustomerGroup::factory()->create([
        'default' => true,
    ]);
});

test('will only apply to lines with correct brand', function () {
    $customerGroup = CustomerGroup::getDefault();

    $channel = Channel::getDefault();

    $currency = Currency::getDefault();

    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
        'coupon_code' => '10OFF',
    ]);

    $brandA = Brand::factory()->create([
        'name' => 'Brand A',
    ]);

    $brandB = Brand::factory()->create([
        'name' => 'Brand B',
    ]);

    $productA = Product::factory()->create([
        'brand_id' => $brandA->id,
    ]);

    $productB = Product::factory()->create([
        'brand_id' => $brandB->id,
    ]);

    $purchasableA = ProductVariant::factory()->create([
        'product_id' => $productA->id,
    ]);
    $purchasableB = ProductVariant::factory()->create([
        'product_id' => $productB->id,
    ]);

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableA),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableA),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 1,
    ]);

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableB),
        'priceable_id' => $purchasableB->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableB),
        'purchasable_id' => $purchasableB->id,
        'quantity' => 1,
    ]);

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'coupon' => '10OFF',
        'data' => [
            'fixed_value' => false,
            'percentage' => 10,
        ],
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
    ]);

    $discount->channels()->sync([
        $channel->id => [
            'enabled' => true,
            'starts_at' => now()->subHour(),
        ],
    ]);

    $discount->brands()->sync([$brandA->id]);

    $cart = $cart->calculate();

    /**
     * Cart has two lines.
     * 1 x $10 / 10% off $9 / 20% tax = $1.8 / Total = 10.80
     * 1 x $10 / 0% off $10 / 20% tax = $2 / Total = 12
     * Cart total = $22.80
     */
    expect($cart->discountTotal->value)->toEqual(100);
    expect($cart->total->value)->toEqual(2280);
});

test('will not apply to lines with excluded brand', function () {
    $customerGroup = CustomerGroup::getDefault();

    $channel = Channel::getDefault();

    $currency = Currency::getDefault();

    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
        'coupon_code' => '10OFF',
    ]);

    $brandA = Brand::factory()->create([
        'name' => 'Brand A',
    ]);

    $brandB = Brand::factory()->create([
        'name' => 'Brand B',
    ]);

    $productA = Product::factory()->create([
        'brand_id' => $brandA->id,
    ]);

    $productB = Product::factory()->create([
        'brand_id' => $brandB->id,
    ]);

    $purchasableA = ProductVariant::factory()->create([
        'product_id' => $productA->id,
    ]);
    $purchasableB = ProductVariant::factory()->create([
        'product_id' => $productB->id,
    ]);

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableA),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableA),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 1,
    ]);

    Price::factory()->create([
        'price' => 2000, // £20
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableB),
        'priceable_id' => $purchasableB->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableB),
        'purchasable_id' => $purchasableB->id,
        'quantity' => 1,
    ]);

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'coupon' => '10OFF',
        'data' => [
            'fixed_value' => false,
            'percentage' => 10,
        ],
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
    ]);

    $discount->channels()->sync([
        $channel->id => [
            'enabled' => true,
            'starts_at' => now()->subHour(),
        ],
    ]);

    $discount->brands()->sync([$brandA->id => ['type' => 'exclusion']]);

    $cart = $cart->calculate();

    /**
     * Cart has two lines.
     * 1 x $10 / 10% off $9 / 20% tax = $1.8 / Total = 10.80
     * 1 x $10 / 0% off $10 / 20% tax = $2 / Total = 12
     * Cart total = $22.80
     */
    expect($cart->discountTotal->value)->toEqual(200);
    expect($cart->total->value)->toEqual(3360);
});

test('will only apply to lines with correct product', function () {
    $customerGroup = CustomerGroup::getDefault();

    $channel = Channel::getDefault();

    $currency = Currency::getDefault();

    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
        'coupon_code' => '10OFF',
    ]);

    $brandA = Brand::factory()->create([
        'name' => 'Brand A',
    ]);

    $productA = Product::factory()->create([
        'brand_id' => $brandA->id,
    ]);

    $productB = Product::factory()->create([
        'brand_id' => $brandA->id,
    ]);

    $purchasableA = ProductVariant::factory()->create([
        'product_id' => $productA->id,
    ]);
    $purchasableB = ProductVariant::factory()->create([
        'product_id' => $productB->id,
    ]);

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableA),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableA),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 1,
    ]);

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableB),
        'priceable_id' => $purchasableB->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableB),
        'purchasable_id' => $purchasableB->id,
        'quantity' => 1,
    ]);

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'coupon' => '10OFF',
        'data' => [
            'fixed_value' => false,
            'percentage' => 10,
        ],
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
    ]);

    $discount->channels()->sync([
        $channel->id => [
            'enabled' => true,
            'starts_at' => now()->subHour(),
        ],
    ]);

    $discount->purchasableLimitations()->create([
        'discount_id' => $discount->id,
        'type' => 'limitation',
        'purchasable_type' => Product::class,
        'purchasable_id' => $productA->id,
    ]);

    $cart = $cart->calculate();

    expect($cart->discountTotal->value)->toEqual(100);
    expect($cart->total->value)->toEqual(2280);
});

test('will only apply to lines with correct product variant', function () {
    $customerGroup = CustomerGroup::getDefault();

    $channel = Channel::getDefault();

    $currency = Currency::getDefault();

    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
        'coupon_code' => '10OFF',
    ]);

    $brandA = Brand::factory()->create([
        'name' => 'Brand A',
    ]);

    $productA = Product::factory()->create([
        'brand_id' => $brandA->id,
    ]);

    $productB = Product::factory()->create([
        'brand_id' => $brandA->id,
    ]);

    $purchasableA = ProductVariant::factory()->create([
        'product_id' => $productA->id,
    ]);
    $purchasableB = ProductVariant::factory()->create([
        'product_id' => $productB->id,
    ]);

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableA),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableA),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 1,
    ]);

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableB),
        'priceable_id' => $purchasableB->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableB),
        'purchasable_id' => $purchasableB->id,
        'quantity' => 1,
    ]);

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'coupon' => '10OFF',
        'data' => [
            'fixed_value' => false,
            'percentage' => 10,
        ],
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
    ]);

    $discount->channels()->sync([
        $channel->id => [
            'enabled' => true,
            'starts_at' => now()->subHour(),
        ],
    ]);

    $discount->purchasableLimitations()->create([
        'discount_id' => $discount->id,
        'type' => 'limitation',
        'purchasable_type' => ProductVariant::class,
        'purchasable_id' => $purchasableA->id,
    ]);

    $cart = $cart->calculate();

    expect($cart->discountTotal->value)->toEqual(100);
    expect($cart->total->value)->toEqual(2280);
});

test('will not apply to lines with excluded product', function () {
    $customerGroup = CustomerGroup::getDefault();

    $channel = Channel::getDefault();

    $currency = Currency::getDefault();

    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
        'coupon_code' => '10OFF',
    ]);

    $brandA = Brand::factory()->create([
        'name' => 'Brand A',
    ]);

    $productA = Product::factory()->create([
        'brand_id' => $brandA->id,
    ]);

    $productB = Product::factory()->create([
        'brand_id' => $brandA->id,
    ]);

    $purchasableA = ProductVariant::factory()->create([
        'product_id' => $productA->id,
    ]);
    $purchasableB = ProductVariant::factory()->create([
        'product_id' => $productB->id,
    ]);

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableA),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableA),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 1,
    ]);

    Price::factory()->create([
        'price' => 2000, // £20
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableB),
        'priceable_id' => $purchasableB->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableB),
        'purchasable_id' => $purchasableB->id,
        'quantity' => 1,
    ]);

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'coupon' => '10OFF',
        'data' => [
            'fixed_value' => false,
            'percentage' => 10,
        ],
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
    ]);

    $discount->channels()->sync([
        $channel->id => [
            'enabled' => true,
            'starts_at' => now()->subHour(),
        ],
    ]);

    $discount->purchasableExclusions()->create([
        'discount_id' => $discount->id,
        'type' => 'exclusion',
        'purchasable_type' => Product::class,
        'purchasable_id' => $productA->id,
    ]);

    $cart = $cart->calculate();

    expect($cart->discountTotal->value)->toEqual(200);
    expect($cart->total->value)->toEqual(3360);
});

test('will not apply to lines with excluded product variant', function () {
    $customerGroup = CustomerGroup::getDefault();

    $channel = Channel::getDefault();

    $currency = Currency::getDefault();

    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
        'coupon_code' => '10OFF',
    ]);

    $brandA = Brand::factory()->create([
        'name' => 'Brand A',
    ]);

    $productA = Product::factory()->create([
        'brand_id' => $brandA->id,
    ]);

    $productB = Product::factory()->create([
        'brand_id' => $brandA->id,
    ]);

    $purchasableA = ProductVariant::factory()->create([
        'product_id' => $productA->id,
    ]);
    $purchasableB = ProductVariant::factory()->create([
        'product_id' => $productB->id,
    ]);

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableA),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableA),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 1,
    ]);

    Price::factory()->create([
        'price' => 2000, // £20
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableB),
        'priceable_id' => $purchasableB->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableB),
        'purchasable_id' => $purchasableB->id,
        'quantity' => 1,
    ]);

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'coupon' => '10OFF',
        'data' => [
            'fixed_value' => false,
            'percentage' => 10,
        ],
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
    ]);

    $discount->channels()->sync([
        $channel->id => [
            'enabled' => true,
            'starts_at' => now()->subHour(),
        ],
    ]);

    $discount->purchasableExclusions()->create([
        'discount_id' => $discount->id,
        'type' => 'exclusion',
        'purchasable_type' => ProductVariant::class,
        'purchasable_id' => $purchasableA->id,
    ]);

    $cart = $cart->calculate();

    expect($cart->discountTotal->value)->toEqual(200);
    expect($cart->total->value)->toEqual(3360);
});

test('can apply fixed amount discount', function () {
    $currency = Currency::getDefault();

    $customerGroup = CustomerGroup::getDefault();

    $channel = Channel::getDefault();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'coupon_code' => '10OFF',
    ]);

    $purchasableA = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableA),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableA),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 2,
    ]);

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'coupon' => '10OFF',
        'data' => [
            'fixed_value' => true,
            'fixed_values' => [
                'GBP' => 10.5,
            ],
        ],
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
    ]);

    $discount->channels()->sync([
        $channel->id => [
            'enabled' => true,
            'starts_at' => now()->subHour(),
        ],
    ]);

    $cart = $cart->calculate();

    expect($cart->discountTotal->value)->toEqual(1050);
    expect($cart->total->value)->toEqual(1140);
    expect($cart->taxTotal->value)->toEqual(190);
    expect($cart->discounts)->toHaveCount(1);
});

test('fixed amount discount distributes across cart lines', function () {
    $currency = Currency::getDefault();

    $customerGroup = CustomerGroup::getDefault();

    $channel = Channel::getDefault();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'coupon_code' => '10OFF',
    ]);

    $purchasableA = ProductVariant::factory()->create();
    $purchasableB = ProductVariant::factory()->create();
    $purchasableC = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableA),
        'priceable_id' => $purchasableA->id,
    ]);

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableB),
        'priceable_id' => $purchasableB->id,
    ]);

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableC),
        'priceable_id' => $purchasableC->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableA),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 1,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableA),
        'purchasable_id' => $purchasableB->id,
        'quantity' => 1,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableA),
        'purchasable_id' => $purchasableC->id,
        'quantity' => 1,
    ]);

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'coupon' => '10OFF',
        'data' => [
            'fixed_value' => true,
            'fixed_values' => [
                'GBP' => 10,
            ],
        ],
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
    ]);

    $discount->channels()->sync([
        $channel->id => [
            'enabled' => true,
            'starts_at' => now()->subHour(),
        ],
    ]);

    $cart = $cart->calculate();

    $firstLine = $cart->lines->first();
    $secondLine = $cart->lines->skip(1)->first();
    $lastLine = $cart->lines->last();

    expect($firstLine->discountTotal->value)->toEqual(334);
    expect($secondLine->discountTotal->value)->toEqual(333);
    expect($lastLine->discountTotal->value)->toEqual(333);
});

test('can apply percentage discount', function () {
    $customerGroup = CustomerGroup::getDefault();

    $channel = Channel::getDefault();

    $currency = Currency::getDefault();

    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
        'coupon_code' => '10PERCENTOFF',
    ]);

    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 1000,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasable),
        'priceable_id' => $purchasable->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasable),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'coupon' => '10PERCENTOFF',
        'data' => [
            'percentage' => 10,
            'fixed_value' => false,
        ],
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
    ]);

    $discount->channels()->sync([
        $channel->id => [
            'enabled' => true,
            'starts_at' => now()->subHour(),
        ],
    ]);

    expect($cart->total)->toBeNull();
    expect($cart->taxTotal)->toBeNull();
    expect($cart->subTotal)->toBeNull();

    $cart = $cart->calculate();

    expect($cart->discountTotal->value)->toEqual(100);
    expect($cart->taxTotal->value)->toEqual(180);
    expect($cart->total->value)->toEqual(1080);

    $cart->lines()->delete();

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasable),
        'purchasable_id' => $purchasable->id,
        'quantity' => 2,
    ]);

    $cart = $cart->refresh()->calculate();

    expect($cart->discountTotal->value)->toEqual(200);
    expect($cart->taxTotal->value)->toEqual(360);
    expect($cart->total->value)->toEqual(2160);
});

test('can only same discount to line once', function () {
    $customerGroup = CustomerGroup::getDefault();

    $channel = Channel::getDefault();

    $currency = Currency::getDefault();

    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
        'coupon_code' => '10PERCENTOFF',
    ]);

    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 1000,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasable),
        'priceable_id' => $purchasable->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasable),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'coupon' => '10PERCENTOFF',
        'data' => [
            'percentage' => 10,
            'fixed_value' => false,
        ],
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
    ]);

    $discount->channels()->sync([
        $channel->id => [
            'enabled' => true,
            'starts_at' => now()->subHour(),
        ],
    ]);

    expect($cart->total)->toBeNull();
    expect($cart->taxTotal)->toBeNull();
    expect($cart->subTotal)->toBeNull();

    $cart = $cart->calculate()->calculate();

    expect($cart->discountTotal->value)->toEqual(100);
    expect($cart->taxTotal->value)->toEqual(180);
    expect($cart->total->value)->toEqual(1080);

    $cart->lines()->delete();

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasable),
        'purchasable_id' => $purchasable->id,
        'quantity' => 2,
    ]);

    $cart = $cart->refresh()->calculate();

    expect($cart->discountTotal->value)->toEqual(200);
    expect($cart->taxTotal->value)->toEqual(360);
    expect($cart->total->value)->toEqual(2160);
});

test('can apply discount without coupon code', function () {
    $currency = Currency::getDefault();

    $customerGroup = CustomerGroup::getDefault();

    $channel = Channel::getDefault();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
    ]);

    $purchasableA = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableA),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableA),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 2,
    ]);

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'coupon' => null,
        'data' => [
            'fixed_value' => true,
            'fixed_values' => [
                'GBP' => 10,
            ],
        ],
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
    ]);

    $discount->channels()->sync([
        $channel->id => [
            'enabled' => true,
            'starts_at' => now()->subHour(),
        ],
    ]);

    $cart = $cart->calculate();

    expect($cart->discountTotal->value)->toEqual(1000);
    expect($cart->total->value)->toEqual(1200);
    expect($cart->taxTotal->value)->toEqual(200);
    expect($cart->discounts)->toHaveCount(1);
});

test('cannot apply discount coupon without coupon code', function () {
    $currency = Currency::getDefault();

    $customerGroup = CustomerGroup::getDefault();

    $channel = Channel::getDefault();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
    ]);

    $purchasableA = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableA),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableA),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 2,
    ]);

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'coupon' => 'OFF10',
        'data' => [
            'fixed_value' => true,
            'fixed_values' => [
                'GBP' => 10,
            ],
        ],
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
    ]);

    $discount->channels()->sync([
        $channel->id => [
            'enabled' => true,
            'starts_at' => now()->subHour(),
        ],
    ]);

    $cart = $cart->calculate();

    expect($cart->discountTotal->value)->toEqual(0);
    expect($cart->total->value)->toEqual(2400);
    expect($cart->taxTotal->value)->toEqual(400);
    expect($cart->discounts->isEmpty())->toBeTrue();
});

test('can apply discount with max uses', function () {
    $currency = Currency::getDefault();

    $customerGroup = CustomerGroup::getDefault();

    $channel = Channel::getDefault();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
    ]);

    $purchasableA = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableA),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableA),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 2,
    ]);

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'uses' => 2,
        'max_uses' => 10,
        'data' => [
            'fixed_value' => true,
            'fixed_values' => [
                'GBP' => 10,
            ],
        ],
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
    ]);

    $discount->channels()->sync([
        $channel->id => [
            'enabled' => true,
            'starts_at' => now()->subHour(),
        ],
    ]);

    $cart = $cart->calculate();

    expect($cart->discountTotal->value)->toEqual(1000);
    expect($cart->total->value)->toEqual(1200);
    expect($cart->taxTotal->value)->toEqual(200);
    expect($cart->discounts)->toHaveCount(1);
});

test('cannot apply discount with max uses', function () {
    $currency = Currency::getDefault();

    $customerGroup = CustomerGroup::getDefault();

    $channel = Channel::getDefault();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
    ]);

    $purchasableA = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableA),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableA),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 2,
    ]);

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'uses' => 10,
        'max_uses' => 10,
        'data' => [
            'fixed_value' => true,
            'fixed_values' => [
                'GBP' => 10,
            ],
        ],
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
    ]);

    $discount->channels()->sync([
        $channel->id => [
            'enabled' => true,
            'starts_at' => now()->subHour(),
        ],
    ]);

    $cart = $cart->calculate();

    expect($cart->discountTotal->value)->toEqual(0);
    expect($cart->total->value)->toEqual(2400);
    expect($cart->subTotal->value)->toEqual(2000);
});

test('can apply discount with min spend', function () {
    $currency = Currency::getDefault();

    $customerGroup = CustomerGroup::getDefault();

    $channel = Channel::getDefault();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
    ]);

    $purchasableA = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableA),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableA),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 10,
    ]);

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'uses' => 2,
        'max_uses' => 10,
        'data' => [
            'fixed_value' => true,
            'fixed_values' => [
                'GBP' => 10,
            ],
            'min_prices' => [
                'GBP' => 50,
            ],
        ],
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
    ]);

    $discount->channels()->sync([
        $channel->id => [
            'enabled' => true,
            'starts_at' => now()->subHour(),
        ],
    ]);

    $cart = $cart->calculate();

    expect($cart->discountTotal->value)->toEqual(1000);
    expect($cart->subTotal->value)->toEqual(10000);
    expect($cart->subTotalDiscounted->value)->toEqual(9000);
    expect($cart->total->value)->toEqual(10800);
    expect($cart->taxTotal->value)->toEqual(1800);
    expect($cart->discounts)->toHaveCount(1);
});

test('cannot apply discount with min spend', function () {
    $currency = Currency::getDefault();

    $customerGroup = CustomerGroup::getDefault();

    $channel = Channel::getDefault();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
    ]);

    $purchasableA = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableA),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableA),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 2,
    ]);

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'uses' => 2,
        'max_uses' => 10,
        'data' => [
            'fixed_value' => true,
            'fixed_values' => [
                'GBP' => 10,
            ],
            'min_prices' => [
                'GBP' => 50,
            ],
        ],
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
    ]);

    $discount->channels()->sync([
        $channel->id => [
            'enabled' => true,
            'starts_at' => now()->subHour(),
        ],
    ]);

    $cart = $cart->calculate();

    expect($cart->discountTotal->value)->toEqual(0);
    expect($cart->subTotal->value)->toEqual(2000);
    expect($cart->total->value)->toEqual(2400);
    expect($cart->taxTotal->value)->toEqual(400);
    expect($cart->discounts->isEmpty())->toBeTrue();
});

test('can apply discount with conditions', function () {
    $currency = Currency::getDefault();

    $customerGroup = CustomerGroup::getDefault();

    $channel = Channel::getDefault();

    $purchasableA = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableA),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'coupon_code' => 'OFF10',
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableA),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 10,
    ]);

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'coupon' => 'OFF10',
        'uses' => 2,
        'max_uses' => 10,
        'data' => [
            'fixed_value' => true,
            'fixed_values' => [
                'GBP' => 10,
            ],
            'min_prices' => [
                'GBP' => 50,
            ],
        ],
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
    ]);

    $discount->channels()->sync([
        $channel->id => [
            'enabled' => true,
            'starts_at' => now()->subHour(),
        ],
    ]);

    $cart = $cart->calculate();

    expect($cart->discountTotal->value)->toEqual(1000);
    expect($cart->subTotal->value)->toEqual(10000);
    expect($cart->subTotalDiscounted->value)->toEqual(9000);
    expect($cart->total->value)->toEqual(10800);
    expect($cart->taxTotal->value)->toEqual(1800);
    expect($cart->discounts)->toHaveCount(1);
});

test('can apply discount with max user uses', function () {
    $currency = Currency::getDefault();

    $customerGroup = CustomerGroup::getDefault();

    $channel = Channel::getDefault();

    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    $customer->customerGroups()->attach($customerGroup);

    $user->customers()->attach($customer);

    $this->actingAs($user);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
    ]);

    $cart->user()->associate($user);

    $purchasableA = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableA),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableA),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 2,
    ]);

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'uses' => 0,
        'max_uses' => 10,
        'max_uses_per_user' => 2,
        'data' => [
            'fixed_value' => true,
            'fixed_values' => [
                'GBP' => 10,
            ],
        ],
    ]);

    $discount->users()->sync([
        $user->id,
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
    ]);

    $discount->channels()->sync([
        $channel->id => [
            'enabled' => true,
            'starts_at' => now()->subHour(),
        ],
    ]);

    $cart = $cart->calculate();

    expect($cart->discountTotal->value)->toEqual(1000);
    expect($cart->total->value)->toEqual(1200);
    expect($cart->subTotal->value)->toEqual(2000);
    expect($cart->subTotalDiscounted->value)->toEqual(1000);
});

test('cannot apply discount with max user uses', function () {
    $currency = Currency::getDefault();

    $customerGroup = CustomerGroup::getDefault();

    $channel = Channel::getDefault();

    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    $customer->customerGroups()->attach($customerGroup);

    $user->customers()->attach($customer);

    $this->actingAs($user);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
    ]);

    $cart->user()->associate($user);

    $purchasableA = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableA),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableA),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 2,
    ]);

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'uses' => 0,
        'max_uses' => 10,
        'max_uses_per_user' => 1,
        'data' => [
            'fixed_value' => true,
            'fixed_values' => [
                'GBP' => 10,
            ],
        ],
    ]);

    $discount->users()->sync([
        $user->id,
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
    ]);

    $discount->channels()->sync([
        $channel->id => [
            'enabled' => true,
            'starts_at' => now()->subHour(),
        ],
    ]);

    $cart = $cart->calculate();

    expect($cart->discountTotal->value)->toEqual(0);
    expect($cart->total->value)->toEqual(2400);
    expect($cart->subTotal->value)->toEqual(2000);
});

test('fixed amount discount distributes across cart lines with different values', function () {
    $currency = Currency::getDefault();

    $customerGroup = CustomerGroup::getDefault();

    $channel = Channel::getDefault();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'coupon_code' => 'DISCOUNTOFF',
    ]);

    $purchasableA = ProductVariant::factory()->create();
    $purchasableB = ProductVariant::factory()->create();
    $purchasableC = ProductVariant::factory()->create();
    $purchasableD = ProductVariant::factory()->create();
    $purchasableE = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 15, // £0.15
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableA),
        'priceable_id' => $purchasableA->id,
    ]);

    Price::factory()->create([
        'price' => 20, // £0.20
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableB),
        'priceable_id' => $purchasableB->id,
    ]);

    Price::factory()->create([
        'price' => 40, // £0.40
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableC),
        'priceable_id' => $purchasableC->id,
    ]);

    Price::factory()->create([
        'price' => 40, // £0.40
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableD),
        'priceable_id' => $purchasableD->id,
    ]);

    Price::factory()->create([
        'price' => 40, // £0.40
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableE),
        'priceable_id' => $purchasableE->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableA),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 10,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableB),
        'purchasable_id' => $purchasableB->id,
        'quantity' => 10,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableC),
        'purchasable_id' => $purchasableC->id,
        'quantity' => 10,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableD),
        'purchasable_id' => $purchasableD->id,
        'quantity' => 10,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableE),
        'purchasable_id' => $purchasableE->id,
        'quantity' => 9,
    ]);

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'coupon' => 'DISCOUNTOFF',
        'data' => [
            'fixed_value' => true,
            'fixed_values' => [
                'GBP' => 15.00,
            ],
        ],
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
    ]);

    $discount->channels()->sync([
        $channel->id => [
            'enabled' => true,
            'starts_at' => now()->subHour(),
        ],
    ]);

    $cart = $cart->calculate();

    $firstLine = $cart->lines->first();
    $secondLine = $cart->lines->skip(1)->first();
    $thirdLine = $cart->lines->skip(2)->first();
    $fourthLine = $cart->lines->skip(3)->first();
    $lastLine = $cart->lines->last();

    expect($firstLine->subTotalDiscounted->value)->toBeGreaterThanOrEqual(0);
    expect($secondLine->subTotalDiscounted->value)->toBeGreaterThanOrEqual(0);
    expect($thirdLine->subTotalDiscounted->value)->toBeGreaterThanOrEqual(0);
    expect($fourthLine->subTotalDiscounted->value)->toBeGreaterThanOrEqual(0);
    expect($lastLine->subTotalDiscounted->value)->toBeGreaterThanOrEqual(0);

    expect($firstLine->discountTotal->value)->toEqual(150);
    expect($secondLine->discountTotal->value)->toEqual(199);
    expect($thirdLine->discountTotal->value)->toEqual(397);
    expect($fourthLine->discountTotal->value)->toEqual(397);
    expect($lastLine->discountTotal->value)->toEqual(357);
    expect($cart->discountTotal->value)->toEqual(1500);
});

test('can apply discount dynamically', function () {
    $currency = Currency::getDefault();

    $customerGroup = CustomerGroup::getDefault();

    $channel = Channel::getDefault();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
    ]);

    $purchasableA = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableA),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableA),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 2,
    ]);

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'coupon' => '10OFF',
        'data' => [
            'fixed_value' => true,
            'fixed_values' => [
                'GBP' => 10.5,
            ],
        ],
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
    ]);

    $discount->channels()->sync([
        $channel->id => [
            'enabled' => true,
            'starts_at' => now()->subHour(),
        ],
    ]);

    // Calculate method called for the first time
    CartSession::use($cart)->calculate();

    // Update cart with coupon code
    $cart->update([
        'coupon_code' => '10OFF',
    ]);

    // Get current cart which runs the calculate method for the second time
    $cart = CartSession::current();

    // Calculate method called for the third time
    $cart = $cart->calculate();

    expect($cart->discountTotal->value)->toEqual(1050);
    expect($cart->total->value)->toEqual(1140);
    expect($cart->taxTotal->value)->toEqual(190);
    expect($cart->discounts)->toHaveCount(1);
});

test('can handle malformed discount', function () {
    $currency = Currency::getDefault();

    $customerGroup = CustomerGroup::getDefault();

    $channel = Channel::getDefault();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
    ]);

    $purchasableA = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableA),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableA),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 2,
    ]);

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'coupon' => '10OFF',
        'data' => [],
    ]);

    // Calculate method called for the first time
    CartSession::use($cart)->calculate();

    // Update cart with coupon code
    $cart->update([
        'coupon_code' => '10OFF',
    ]);

    // Get current cart which runs the calculate method for the second time
    $cart = CartSession::current();

    // Calculate method called for the third time
    $cart = $cart->calculate();

    expect($cart->discountTotal->value)->toEqual(0);
})->group('this');
