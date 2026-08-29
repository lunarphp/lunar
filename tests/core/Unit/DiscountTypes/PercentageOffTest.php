<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\DiscountTypes\PercentageOff;
use Lunar\Core\Facades\CartSession;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
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
        'priceable_type' => $purchasableA->getMorphClass(),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableA->getMorphClass(),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 1,
    ]);

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasableB->getMorphClass(),
        'priceable_id' => $purchasableB->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableB->getMorphClass(),
        'purchasable_id' => $purchasableB->id,
        'quantity' => 1,
    ]);

    $discount = Discount::factory()->create([
        'type' => PercentageOff::class,
        'name' => 'Test Coupon',
        'coupon' => '10OFF',
        'data' => [
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
        'priceable_type' => $purchasableA->getMorphClass(),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableA->getMorphClass(),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 1,
    ]);

    Price::factory()->create([
        'price' => 2000, // £20
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasableB->getMorphClass(),
        'priceable_id' => $purchasableB->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableB->getMorphClass(),
        'purchasable_id' => $purchasableB->id,
        'quantity' => 1,
    ]);

    $discount = Discount::factory()->create([
        'type' => PercentageOff::class,
        'name' => 'Test Coupon',
        'coupon' => '10OFF',
        'data' => [
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
        'priceable_type' => $purchasableA->getMorphClass(),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableA->getMorphClass(),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 1,
    ]);

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasableB->getMorphClass(),
        'priceable_id' => $purchasableB->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableB->getMorphClass(),
        'purchasable_id' => $purchasableB->id,
        'quantity' => 1,
    ]);

    $discount = Discount::factory()->create([
        'type' => PercentageOff::class,
        'name' => 'Test Coupon',
        'coupon' => '10OFF',
        'data' => [
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

    $discount->discountableLimitations()->create([
        'discount_id' => $discount->id,
        'type' => 'limitation',
        'discountable_type' => $productA->getMorphClass(),
        'discountable_id' => $productA->id,
    ]);

    $cart = $cart->calculate();

    expect($cart->discountTotal->value)->toEqual(100);
    expect($cart->total->value)->toEqual(2280);
})->group('thisone');

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
        'priceable_type' => $purchasableA->getMorphClass(),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableA->getMorphClass(),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 1,
    ]);

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasableB->getMorphClass(),
        'priceable_id' => $purchasableB->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableB->getMorphClass(),
        'purchasable_id' => $purchasableB->id,
        'quantity' => 1,
    ]);

    $discount = Discount::factory()->create([
        'type' => PercentageOff::class,
        'name' => 'Test Coupon',
        'coupon' => '10OFF',
        'data' => [
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

    $discount->discountableLimitations()->create([
        'discount_id' => $discount->id,
        'type' => 'limitation',
        'discountable_type' => $purchasableA->getMorphClass(),
        'discountable_id' => $purchasableA->id,
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
        'priceable_type' => $purchasableA->getMorphClass(),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableA->getMorphClass(),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 1,
    ]);

    Price::factory()->create([
        'price' => 2000, // £20
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasableB->getMorphClass(),
        'priceable_id' => $purchasableB->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableB->getMorphClass(),
        'purchasable_id' => $purchasableB->id,
        'quantity' => 1,
    ]);

    $discount = Discount::factory()->create([
        'type' => PercentageOff::class,
        'name' => 'Test Coupon',
        'coupon' => '10OFF',
        'data' => [
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

    $discount->discountableExclusions()->create([
        'discount_id' => $discount->id,
        'type' => 'exclusion',
        'discountable_type' => $productA->getMorphClass(),
        'discountable_id' => $productA->id,
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
        'priceable_type' => $purchasableA->getMorphClass(),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableA->getMorphClass(),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 1,
    ]);

    Price::factory()->create([
        'price' => 2000, // £20
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasableB->getMorphClass(),
        'priceable_id' => $purchasableB->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableB->getMorphClass(),
        'purchasable_id' => $purchasableB->id,
        'quantity' => 1,
    ]);

    $discount = Discount::factory()->create([
        'type' => PercentageOff::class,
        'name' => 'Test Coupon',
        'coupon' => '10OFF',
        'data' => [
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

    $discount->discountableExclusions()->create([
        'discount_id' => $discount->id,
        'type' => 'exclusion',
        'discountable_type' => $purchasableA->getMorphClass(),
        'discountable_id' => $purchasableA->id,
    ]);

    $cart = $cart->calculate();

    expect($cart->discountTotal->value)->toEqual(200);
    expect($cart->total->value)->toEqual(3360);
});

test('can apply percentage discount', function (
    string $coupon,
    float $percentage,
    int $discountTotalForOne,
    int $taxTotalForOne,
    int $totalForOne,
    int $discountTotalForTwo,
    int $taxTotalForTwo,
    int $totalForTwo
) {
    $customerGroup = CustomerGroup::getDefault();

    $channel = Channel::getDefault();

    $currency = Currency::getDefault();

    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
        'coupon_code' => $coupon,
    ]);

    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 1000,
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

    $discount = Discount::factory()->create([
        'type' => PercentageOff::class,
        'name' => 'Test Coupon',
        'coupon' => $coupon,
        'data' => [
            'percentage' => $percentage,
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

    expect($cart->discountTotal->value)->toEqual($discountTotalForOne);
    expect($cart->taxTotal->value)->toEqual($taxTotalForOne);
    expect($cart->total->value)->toEqual($totalForOne);

    $cart->lines()->delete();

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 2,
    ]);

    $cart = $cart->refresh()->calculate();

    expect($cart->discountTotal->value)->toEqual($discountTotalForTwo);
    expect($cart->taxTotal->value)->toEqual($taxTotalForTwo);
    expect($cart->total->value)->toEqual($totalForTwo);
})->with([
    '10% Discount' => ['10PERCENTOFF', 10, 100, 180, 1080, 200, 360, 2160],
    '10.25% Discount' => ['10PT25PERCENTOFF', 10.25, 103, 179, 1076, 205, 359, 2154],
    '10.5% Discount' => ['10PT5PERCENTOFF', 10.5, 105, 179, 1074, 210, 358, 2148],
]);

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
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    $discount = Discount::factory()->create([
        'type' => PercentageOff::class,
        'name' => 'Test Coupon',
        'coupon' => '10PERCENTOFF',
        'data' => [
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

    expect($cart->total)->toBeNull();
    expect($cart->taxTotal)->toBeNull();
    expect($cart->subTotal)->toBeNull();

    $cart = $cart->calculate()->calculate();

    expect($cart->discountTotal->value)->toEqual(100);
    expect($cart->taxTotal->value)->toEqual(180);
    expect($cart->total->value)->toEqual(1080);

    $cart->lines()->delete();

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 2,
    ]);

    $cart = $cart->refresh()->calculate();

    expect($cart->discountTotal->value)->toEqual(200);
    expect($cart->taxTotal->value)->toEqual(360);
    expect($cart->total->value)->toEqual(2160);
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
        'priceable_type' => $purchasableA->getMorphClass(),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableA->getMorphClass(),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 2,
    ]);

    $discount = Discount::factory()->create([
        'type' => PercentageOff::class,
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
});

test('can apply multiple discounts to the same line', function () {
    $customerGroup = CustomerGroup::getDefault();

    $channel = Channel::getDefault();

    $currency = Currency::getDefault();

    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 1000,
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

    $discounts = Discount::factory()->createMany([
        [
            'type' => PercentageOff::class,
            'name' => '10% discount',
            'data' => [
                'percentage' => 10,
            ],
        ],
        [
            'type' => PercentageOff::class,
            'name' => '20% discount',
            'data' => [
                'percentage' => 20,
            ],
        ],
    ]);

    foreach ($discounts as $discount) {
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
    }

    $cart = $cart->calculate();

    /**
     * Cart has two discounts.
     * 1 x $10 / 10% off $9
     * 1 x $9 / 20% off $7.20
     * Cart total = $7.20 / 20% tax = $1.44 / Total = $8.64
     */
    expect($cart->discountBreakdown)->toHaveCount(2);
    expect($cart->discountBreakdown->get(0)->price->value)->toEqual(100);
    expect($cart->discountBreakdown->get(1)->price->value)->toEqual(180);
    expect($cart->lines->first()->discountTotal->value)->toEqual(280);
    expect($cart->subTotal->value)->toEqual(1000);
    expect($cart->subTotalDiscounted->value)->toEqual(720);
    expect($cart->discountTotal->value)->toEqual(280);
    expect($cart->total->value)->toEqual(864);
});
