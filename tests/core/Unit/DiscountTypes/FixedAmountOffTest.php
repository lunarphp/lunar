<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\DiscountTypes\FixedAmountOff;
use Lunar\Core\Facades\CartSession;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\Stubs\User;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

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
        'priceable_type' => $purchasableA->getMorphClass(),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableA->getMorphClass(),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 2,
    ]);

    $discount = Discount::factory()->create([
        'type' => FixedAmountOff::class,
        'name' => 'Test Coupon',
        'coupon' => '10OFF',
        'data' => [
            'amounts' => [
                'GBP' => 1050,
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
        'priceable_type' => $purchasableA->getMorphClass(),
        'priceable_id' => $purchasableA->id,
    ]);

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasableB->getMorphClass(),
        'priceable_id' => $purchasableB->id,
    ]);

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasableC->getMorphClass(),
        'priceable_id' => $purchasableC->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableA->getMorphClass(),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 1,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableA->getMorphClass(),
        'purchasable_id' => $purchasableB->id,
        'quantity' => 1,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableA->getMorphClass(),
        'purchasable_id' => $purchasableC->id,
        'quantity' => 1,
    ]);

    $discount = Discount::factory()->create([
        'type' => FixedAmountOff::class,
        'name' => 'Test Coupon',
        'coupon' => '10OFF',
        'data' => [
            'amounts' => [
                'GBP' => 1000,
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

test('can apply discount without coupon code', function () {
    $currency = Currency::getDefault();

    $customerGroup = CustomerGroup::getDefault();

    $channel = Channel::getDefault();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'coupon_code' => 'NOTAPPLICABLE',
    ]);

    $purchasableA = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasableA->getMorphClass(),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableA->getMorphClass(),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 2,
    ]);

    $discount = Discount::factory()->create([
        'type' => FixedAmountOff::class,
        'name' => 'Test Coupon',
        'coupon' => null,
        'data' => [
            'amounts' => [
                'GBP' => 1000,
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
        'priceable_type' => $purchasableA->getMorphClass(),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableA->getMorphClass(),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 2,
    ]);

    $discount = Discount::factory()->create([
        'type' => FixedAmountOff::class,
        'name' => 'Test Coupon',
        'coupon' => 'OFF10',
        'data' => [
            'amounts' => [
                'GBP' => 1000,
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
        'priceable_type' => $purchasableA->getMorphClass(),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableA->getMorphClass(),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 2,
    ]);

    $discount = Discount::factory()->create([
        'type' => FixedAmountOff::class,
        'name' => 'Test Coupon',
        'uses' => 2,
        'max_uses' => 10,
        'data' => [
            'amounts' => [
                'GBP' => 1000,
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
        'priceable_type' => $purchasableA->getMorphClass(),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableA->getMorphClass(),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 2,
    ]);

    $discount = Discount::factory()->create([
        'type' => FixedAmountOff::class,
        'name' => 'Test Coupon',
        'uses' => 10,
        'max_uses' => 10,
        'data' => [
            'amounts' => [
                'GBP' => 1000,
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
        'priceable_type' => $purchasableA->getMorphClass(),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableA->getMorphClass(),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 10,
    ]);

    $discount = Discount::factory()->create([
        'type' => FixedAmountOff::class,
        'name' => 'Test Coupon',
        'uses' => 2,
        'max_uses' => 10,
        'data' => [
            'amounts' => [
                'GBP' => 1000,
            ],
            'min_prices' => [
                'GBP' => 5000,
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
        'priceable_type' => $purchasableA->getMorphClass(),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableA->getMorphClass(),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 2,
    ]);

    $discount = Discount::factory()->create([
        'type' => FixedAmountOff::class,
        'name' => 'Test Coupon',
        'uses' => 2,
        'max_uses' => 10,
        'data' => [
            'amounts' => [
                'GBP' => 1000,
            ],
            'min_prices' => [
                'GBP' => 5000,
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
        'priceable_type' => $purchasableA->getMorphClass(),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'coupon_code' => 'OFF10',
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableA->getMorphClass(),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 10,
    ]);

    $discount = Discount::factory()->create([
        'type' => FixedAmountOff::class,
        'name' => 'Test Coupon',
        'coupon' => 'OFF10',
        'uses' => 2,
        'max_uses' => 10,
        'data' => [
            'amounts' => [
                'GBP' => 1000,
            ],
            'min_prices' => [
                'GBP' => 5000,
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
        'priceable_type' => $purchasableA->getMorphClass(),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableA->getMorphClass(),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 2,
    ]);

    $discount = Discount::factory()->create([
        'type' => FixedAmountOff::class,
        'name' => 'Test Coupon',
        'uses' => 0,
        'max_uses' => 10,
        'max_uses_per_user' => 2,
        'data' => [
            'amounts' => [
                'GBP' => 1000,
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
        'priceable_type' => $purchasableA->getMorphClass(),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableA->getMorphClass(),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 2,
    ]);

    $discount = Discount::factory()->create([
        'type' => FixedAmountOff::class,
        'name' => 'Test Coupon',
        'uses' => 0,
        'max_uses' => 10,
        'max_uses_per_user' => 1,
        'data' => [
            'amounts' => [
                'GBP' => 1000,
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
        'priceable_type' => $purchasableA->getMorphClass(),
        'priceable_id' => $purchasableA->id,
    ]);

    Price::factory()->create([
        'price' => 20, // £0.20
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasableB->getMorphClass(),
        'priceable_id' => $purchasableB->id,
    ]);

    Price::factory()->create([
        'price' => 40, // £0.40
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasableC->getMorphClass(),
        'priceable_id' => $purchasableC->id,
    ]);

    Price::factory()->create([
        'price' => 40, // £0.40
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasableD->getMorphClass(),
        'priceable_id' => $purchasableD->id,
    ]);

    Price::factory()->create([
        'price' => 40, // £0.40
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasableE->getMorphClass(),
        'priceable_id' => $purchasableE->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableA->getMorphClass(),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 10,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableB->getMorphClass(),
        'purchasable_id' => $purchasableB->id,
        'quantity' => 10,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableC->getMorphClass(),
        'purchasable_id' => $purchasableC->id,
        'quantity' => 10,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableD->getMorphClass(),
        'purchasable_id' => $purchasableD->id,
        'quantity' => 10,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableE->getMorphClass(),
        'purchasable_id' => $purchasableE->id,
        'quantity' => 9,
    ]);

    $discount = Discount::factory()->create([
        'type' => FixedAmountOff::class,
        'name' => 'Test Coupon',
        'coupon' => 'DISCOUNTOFF',
        'data' => [
            'amounts' => [
                'GBP' => 1500,
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

    // Largest-remainder allocation: subtotals 150/200/400/400/360, total 1510,
    // discount 1500. Floors 148/198/397/397/357 sum to 1497; +1 goes to the
    // three lines with the largest fractional remainders (A, B, E).
    expect($firstLine->discountTotal->value)->toEqual(149);
    expect($secondLine->discountTotal->value)->toEqual(199);
    expect($thirdLine->discountTotal->value)->toEqual(397);
    expect($fourthLine->discountTotal->value)->toEqual(397);
    expect($lastLine->discountTotal->value)->toEqual(358);
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
        'priceable_type' => $purchasableA->getMorphClass(),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableA->getMorphClass(),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 2,
    ]);

    $discount = Discount::factory()->create([
        'type' => FixedAmountOff::class,
        'name' => 'Test Coupon',
        'coupon' => '10OFF',
        'data' => [
            'amounts' => [
                'GBP' => 1050,
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
    $cart = $cart->recalculate();

    expect($cart->discountTotal->value)->toEqual(1050);
    expect($cart->total->value)->toEqual(1140);
    expect($cart->taxTotal->value)->toEqual(190);
    expect($cart->discounts)->toHaveCount(1);
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

    foreach ([$purchasableA, $purchasableB] as $purchasable) {
        Price::factory()->create([
            'price' => 1000, // GBP 10
            'min_quantity' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => $purchasable->getMorphClass(),
            'priceable_id' => $purchasable->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => $purchasable->getMorphClass(),
            'purchasable_id' => $purchasable->id,
            'quantity' => 1,
        ]);
    }

    $discount = Discount::factory()->create([
        'type' => FixedAmountOff::class,
        'name' => 'Test Coupon',
        'coupon' => '10OFF',
        'data' => [
            'amounts' => [
                'GBP' => 100,
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

    $discount->brands()->sync([$brandA->id]);

    $cart = $cart->calculate();

    // Only brand A's line is eligible, so the whole GBP 1.00 lands on it.
    expect($cart->discountTotal->value)->toEqual(100);
    expect($cart->lines->first()->discountTotal->value)->toEqual(100);
    expect($cart->lines->last()->discountTotal->value)->toEqual(0);
});
