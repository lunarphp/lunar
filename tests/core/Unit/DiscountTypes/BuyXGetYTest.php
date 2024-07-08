<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Lunar\DiscountTypes\AmountOff;
use Lunar\DiscountTypes\BuyXGetY;
use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Discount;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can determine correct reward qty', function ($linesQuantity, $minQty, $rewardQty, $maxRewardQty, $expected) {
    $driver = new BuyXGetY;

    expect($driver->getRewardQuantity(
        $linesQuantity,
        $minQty,
        $rewardQty,
        $maxRewardQty ?? null
    ))->toEqual($expected);
})->with('provideRewardChecks');

dataset('provideRewardChecks', function () {
    return [
        [
            'linesQuantity' => 1,
            'minQty' => 1,
            'rewardQty' => 1,
            'maxRewardQty' => null,
            'expected' => 1,
        ],
        [
            'linesQuantity' => 2,
            'minQty' => 1,
            'rewardQty' => 1,
            'maxRewardQty' => null,
            'expected' => 2,
        ],
        [
            'linesQuantity' => 2,
            'minQty' => 2,
            'rewardQty' => 1,
            'maxRewardQty' => null,
            'expected' => 1,
        ],
        [
            'linesQuantity' => 10,
            'minQty' => 10,
            'rewardQty' => 1,
            'maxRewardQty' => null,
            'expected' => 1,
        ],
        [
            'linesQuantity' => 10,
            'minQty' => 1,
            'rewardQty' => 1,
            'maxRewardQty' => null,
            'expected' => 10,
        ],
        [
            'linesQuantity' => 10,
            'minQty' => 1,
            'rewardQty' => 1,
            'maxRewardQty' => 5,
            'expected' => 5,
        ],
        [
            'linesQuantity' => 3,
            'minQty' => 2,
            'rewardQty' => 1,
            'maxRewardQty' => 10,
            'expected' => 1,
        ],
        [
            'linesQuantity' => 0,
            'minQty' => 1,
            'rewardQty' => 1,
            'maxRewardQty' => null,
            'expected' => 0,
        ],
        [
            'linesQuantity' => 4,
            'minQty' => 5,
            'rewardQty' => 3,
            'maxRewardQty' => null,
            'expected' => 0,
        ],
        [
            'linesQuantity' => 5,
            'minQty' => 5,
            'rewardQty' => 3,
            'maxRewardQty' => null,
            'expected' => 3,
        ],
        [
            'linesQuantity' => 10,
            'minQty' => 5,
            'rewardQty' => 3,
            'maxRewardQty' => null,
            'expected' => 6,
        ],
        [
            'linesQuantity' => 10,
            'minQty' => 5,
            'rewardQty' => 3,
            'maxRewardQty' => 5,
            'expected' => 5,
        ],
    ];
});

test('can discount eligible product', function () {
    $customerGroup = CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $channel = Channel::factory()->create([
        'default' => true,
    ]);

    $currency = Currency::factory()->create([
        'code' => 'GBP',
    ]);

    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
    ]);

    $productA = Product::factory()->create();

    $productB = Product::factory()->create();

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
        'type' => BuyXGetY::class,
        'name' => 'Test Product Discount',
        'data' => [
            'min_qty' => 1,
            'reward_qty' => 1,
        ],
    ]);

    $discount->purchasableLimitations()->create([
        'purchasable_type' => $productA->getMorphClass(),
        'purchasable_id' => $productA->id,
    ]);

    $discount->purchasableRewards()->create([
        'purchasable_type' => $productB->getMorphClass(),
        'purchasable_id' => $productB->id,
        'type' => 'reward',
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

    $purchasableBCartLine = $cart->lines->first(function ($line) use ($purchasableB) {
        return $line->purchasable_id == $purchasableB->id;
    });

    expect($purchasableBCartLine->discountTotal->value)->toEqual(1000);
});

test('can discount eligible products', function () {
    $customerGroup = CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $channel = Channel::factory()->create([
        'default' => true,
    ]);

    $currency = Currency::factory()->create([
        'code' => 'GBP',
    ]);

    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
    ]);

    $productA = Product::factory()->create();
    $productB = Product::factory()->create();

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

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasableB->getMorphClass(),
        'priceable_id' => $purchasableB->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableA->getMorphClass(),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 1,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableB->getMorphClass(),
        'purchasable_id' => $purchasableB->id,
        'quantity' => 1,
    ]);

    $discount = Discount::factory()->create([
        'type' => BuyXGetY::class,
        'name' => 'Test Product Discount',
        'data' => [
            'min_qty' => 1,
            'reward_qty' => 2,
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

    $discount->purchasableConditions()->create([
        'purchasable_type' => $productA->getMorphClass(),
        'purchasable_id' => $productA->id,
    ]);

    $discount->purchasableRewards()->create([
        'purchasable_type' => $productB->getMorphClass(),
        'purchasable_id' => $productB->id,
        'type' => 'reward',
    ]);

    $cart = $cart->calculate();

    expect($cart->total->value)->toEqual(1200);
    expect($cart->freeItems)->toHaveCount(1);
});

test('can discount purchasable with priority', function () {
    $customerGroup = CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $channel = Channel::factory()->create([
        'default' => true,
    ]);

    $currency = Currency::factory()->create([
        'code' => 'GBP',
    ]);

    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
    ]);

    $productA = Product::factory()->create();
    $productB = Product::factory()->create();

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

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasableB->getMorphClass(),
        'priceable_id' => $purchasableB->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableA->getMorphClass(),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 1,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableB->getMorphClass(),
        'purchasable_id' => $purchasableB->id,
        'quantity' => 1,
    ]);

    $discount = Discount::factory()->create([
        'type' => BuyXGetY::class,
        'priority' => 2,
        'name' => 'Test Product Discount',
        'data' => [
            'min_qty' => 1,
            'reward_qty' => 2,
        ],
    ]);

    Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test A amount off',
        'uses' => 0,
        'priority' => 1,
        'max_uses' => 1,
        'data' => [
            'fixed_value' => true,
            'fixed_values' => [
                'GBP' => 10,
            ],
            'min_prices' => [
                'GBP' => 0,
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

    $discount->purchasableConditions()->create([
        'purchasable_type' => $productA->getMorphClass(),
        'purchasable_id' => $productA->id,
    ]);

    $discount->purchasableRewards()->create([
        'purchasable_type' => $productB->getMorphClass(),
        'purchasable_id' => $productB->id,
        'type' => 'reward',
    ]);

    $cart = $cart->calculate();

    expect($cart->total->value)->toEqual(1200);
    expect($cart->freeItems)->toHaveCount(1);
});

test('can apply multiple different discounts', function () {
    $customerGroup = CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $channel = Channel::factory()->create([
        'default' => true,
    ]);

    $currency = Currency::factory()->create([
        'code' => 'GBP',
    ]);

    /**
     * Product set up.
     */
    $productA = Product::factory()->create();
    $productB = Product::factory()->create();

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

    Price::factory()->create([
        'price' => 500, // £5
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasableB->getMorphClass(),
        'priceable_id' => $purchasableB->id,
    ]);

    /**
     * Cart set up.
     */
    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
        'coupon_code' => 'AMOUNTOFFTEST',
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableA->getMorphClass(),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 2,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableB->getMorphClass(),
        'purchasable_id' => $purchasableB->id,
        'quantity' => 2,
    ]);

    /**
     * Discount set up.
     */
    $discountA = Discount::factory()->create([
        'type' => BuyXGetY::class,
        'priority' => 1,
        'name' => 'Test Product Discount',
        'data' => [
            'min_qty' => 2,
            'reward_qty' => 1,
        ],
    ]);

    $discountB = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test amount off',
        'uses' => 0,
        'priority' => 2,
        'max_uses' => 1,
        'coupon' => 'AMOUNTOFFTEST',
        'data' => [
            'fixed_value' => false,
            'percentage' => 10,
        ],
    ]);

    foreach ([$discountA, $discountB] as $discount) {
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

    $discountA->purchasableConditions()->create([
        'purchasable_type' => $productA->getMorphClass(),
        'purchasable_id' => $productA->id,
    ]);

    $discountA->purchasableRewards()->create([
        'purchasable_type' => $productB->getMorphClass(),
        'purchasable_id' => $productB->id,
        'type' => 'reward',
    ]);

    $cart = $cart->calculate();

    $lineA = $cart->lines->first(function ($line) use ($purchasableA) {
        return $line->purchasable_id == $purchasableA->id;
    });

    $lineB = $cart->lines->first(function ($line) use ($purchasableB) {
        return $line->purchasable_id == $purchasableB->id;
    });

    expect($lineA->subTotal->value)->toEqual(2000);
    expect($lineA->subTotalDiscounted->value)->toEqual(1800);
    expect($lineA->discountTotal->value)->toEqual(200);

    expect($lineB->subTotal->value)->toEqual(1000);
    expect($lineB->subTotalDiscounted->value)->toEqual(500);
    expect($lineB->discountTotal->value)->toEqual(500);

    expect($cart->discountTotal->value)->toEqual(700);
    expect($cart->discountBreakdown)->toHaveCount(2);
});

test('can supplement correct quantities', function () {
    $customerGroup = CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $channel = Channel::factory()->create([
        'default' => true,
    ]);

    $currency = Currency::factory()->create([
        'code' => 'GBP',
    ]);

    $productA = Product::factory()->create();

    $productB = Product::factory()->create();

    $purchasableA = ProductVariant::factory()->create([
        'product_id' => $productA->id,
    ]);
    $purchasableB = ProductVariant::factory()->create([
        'product_id' => $productB->id,
    ]);

    Price::factory()->create([
        'price' => 1064, // $10.64
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasableA->getMorphClass(),
        'priceable_id' => $purchasableA->id,
    ]);

    Price::factory()->create([
        'price' => 2280, // $22.80
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasableB->getMorphClass(),
        'priceable_id' => $purchasableB->id,
    ]);

    $discount = Discount::factory()->create([
        'type' => BuyXGetY::class,
        'priority' => 1,
        'name' => 'Test Product Discount',
        'data' => [
            'min_qty' => 30,
            'reward_qty' => 1,
            'max_reward_qty' => 999,
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

    $discount->purchasableConditions()->create([
        'purchasable_type' => $productA->getMorphClass(),
        'purchasable_id' => $productA->id,
    ]);

    $discount->purchasableRewards()->create([
        'purchasable_type' => $productB->getMorphClass(),
        'purchasable_id' => $productB->id,
        'type' => 'reward',
    ]);

    $lines = [
        [
            'condition_quantity' => 60,
            'reward_quantity' => 3,
            'expected_discount' => (2280 * 2),
            'expected_subtotal' => 2280,
        ],
        [
            'condition_quantity' => 60,
            'reward_quantity' => 4,
            'expected_discount' => (2280 * 2),
            'expected_subtotal' => (2280 * 2),
        ],
        [
            'condition_quantity' => 120,
            'reward_quantity' => 4,
            'expected_discount' => (2280 * 4),
            'expected_subtotal' => 0,
        ],
        [
            'condition_quantity' => 120,
            'reward_quantity' => 1,
            'expected_discount' => 2280,
            'expected_subtotal' => 0,
        ],
    ];

    foreach ($lines as $line) {
        $cart = Cart::factory()->create([
            'channel_id' => $channel->id,
            'currency_id' => $currency->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => $purchasableA->getMorphClass(),
            'purchasable_id' => $purchasableA->id,
            'quantity' => $line['condition_quantity'],
        ]);

        $cart->lines()->create([
            'purchasable_type' => $purchasableB->getMorphClass(),
            'purchasable_id' => $purchasableB->id,
            'quantity' => $line['reward_quantity'],
        ]);

        $cart = $cart->calculate();

        $discountedLine = $cart->lines->first(function ($line) use ($purchasableB) {
            return $line->purchasable_id == $purchasableB->id;
        });
        expect($discountedLine->discountTotal->value)->toEqual($line['expected_discount']);
        expect($discountedLine->subTotalDiscounted->value)->toEqual($line['expected_subtotal']);
    }
});

test('can count condition qty in discount breakdown', function () {
    $customerGroup = CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $channel = Channel::factory()->create([
        'default' => true,
    ]);

    $currency = Currency::factory()->create([
        'code' => 'GBP',
    ]);

    /**
     * Product set up.
     */
    $productA = Product::factory()->create();
    $productB = Product::factory()->create();
    $productC = Product::factory()->create();

    $purchasableA = ProductVariant::factory()->create([
        'product_id' => $productA->id,
    ]);
    $purchasableB = ProductVariant::factory()->create([
        'product_id' => $productB->id,
    ]);
    $purchasableC = ProductVariant::factory()->create([
        'product_id' => $productC->id,
    ]);

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasableA->getMorphClass(),
        'priceable_id' => $purchasableA->id,
    ]);

    Price::factory()->create([
        'price' => 500, // £5
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasableB->getMorphClass(),
        'priceable_id' => $purchasableB->id,
    ]);

    Price::factory()->create([
        'price' => 200, // £2
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasableC->getMorphClass(),
        'priceable_id' => $purchasableC->id,
    ]);

    /**
     * Cart set up.
     */
    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableA->getMorphClass(),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 1,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableB->getMorphClass(),
        'purchasable_id' => $purchasableB->id,
        'quantity' => 1,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableC->getMorphClass(),
        'purchasable_id' => $purchasableC->id,
        'quantity' => 1,
    ]);

    /**
     * Discount set up.
     */
    $discountA = Discount::factory()->create([
        'type' => BuyXGetY::class,
        'priority' => 1,
        'name' => 'Test Product Discount',
        'data' => [
            'min_qty' => 3,
            'reward_qty' => 1,
        ],
    ]);

    foreach ([$discountA] as $discount) {
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

    $discountA->purchasableConditions()->create([
        'purchasable_type' => $productA->getMorphClass(),
        'purchasable_id' => $productA->id,
    ]);

    $discountA->purchasableConditions()->create([
        'purchasable_type' => $productB->getMorphClass(),
        'purchasable_id' => $productB->id,
    ]);

    $discountA->purchasableConditions()->create([
        'purchasable_type' => $productC->getMorphClass(),
        'purchasable_id' => $productC->id,
    ]);

    $discountA->purchasableRewards()->create([
        'purchasable_type' => $productC->getMorphClass(),
        'purchasable_id' => $productC->id,
        'type' => 'reward',
    ]);

    $cart = $cart->calculate();

    $lineA = $cart->lines->first(function ($line) use ($purchasableA) {
        return $line->purchasable_id == $purchasableA->id;
    });

    $lineB = $cart->lines->first(function ($line) use ($purchasableB) {
        return $line->purchasable_id == $purchasableB->id;
    });

    $lineC = $cart->lines->first(function ($line) use ($purchasableC) {
        return $line->purchasable_id == $purchasableC->id;
    });

    expect($lineA->subTotal->value)->toEqual(1000);
    expect($lineA->discountTotal->value)->toEqual(0);

    expect($lineB->subTotal->value)->toEqual(500);
    expect($lineB->discountTotal->value)->toEqual(0);

    expect($lineC->subTotal->value)->toEqual(200);
    expect($lineC->subTotalDiscounted->value)->toEqual(0);
    expect($lineC->discountTotal->value)->toEqual(200);

    expect($cart->subTotal->value)->toEqual(1700);
    expect($cart->discountTotal->value)->toEqual(200);
    expect($cart->total->value)->toEqual(1800);
    expect($cart->discountBreakdown)->toHaveCount(1);
    expect($cart->discountBreakdown->first()->lines)->toHaveCount(3);
});

test('can add eligible products when not in cart', function () {
    $customerGroup = CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $channel = Channel::factory()->create([
        'default' => true,
    ]);

    $currency = Currency::factory()->create([
        'code' => 'GBP',
    ]);

    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
    ]);

    $productA = Product::factory()->create();
    $productB = Product::factory()->create();

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

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasableB->getMorphClass(),
        'priceable_id' => $purchasableB->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableA->getMorphClass(),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 1,
    ]);

    $discount = Discount::factory()->create([
        'type' => BuyXGetY::class,
        'name' => 'Test Product Discount',
        'data' => [
            'min_qty' => 1,
            'reward_qty' => 2,
            'automatically_add_rewards' => true,
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

    $discount->purchasableConditions()->create([
        'purchasable_type' => $productA->getMorphClass(),
        'purchasable_id' => $productA->id,
    ]);

    $discount->purchasableRewards()->create([
        'purchasable_type' => $productB->getMorphClass(),
        'purchasable_id' => $productB->id,
        'type' => 'reward',
    ]);

    $cart = $cart->calculate();

    $this->assertEquals(1200, $cart->total->value);
    $this->assertCount(1, $cart->freeItems);

});
