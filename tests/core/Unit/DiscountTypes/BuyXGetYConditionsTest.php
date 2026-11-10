<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\DiscountTypes\BuyXGetY;
use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Discount;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

/**
 * `AmountOff::apply()` guards on `checkDiscountConditions()`; `BuyXGetY::apply()`
 * does not, so it applies whenever the quantity condition is met.
 *
 * Dates, `max_uses`, the coupon and the channel are already enforced by the
 * query in `DiscountManager::getDiscounts()`, so those still hold. What is only
 * checked in `checkDiscountConditions()` — the minimum spend, customer
 * restrictions and `max_uses_per_user` — is ignored entirely.
 */
function buyXGetYConditionCart(array $discountData, array $discountAttributes = []): Cart
{
    $customerGroup = CustomerGroup::factory()->create(['default' => true]);
    $channel = Channel::factory()->create(['default' => true]);
    $currency = Currency::factory()->create(['code' => 'GBP', 'default' => true]);

    $conditionProduct = Product::factory()->create();
    $rewardProduct = Product::factory()->create();

    $condition = ProductVariant::factory()->create(['product_id' => $conditionProduct->id]);
    $reward = ProductVariant::factory()->create(['product_id' => $rewardProduct->id]);

    foreach ([$condition, $reward] as $variant) {
        Price::factory()->create([
            'price' => 1000, // £10
            'min_quantity' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => $variant->getMorphClass(),
            'priceable_id' => $variant->id,
        ]);
    }

    $discount = Discount::factory()->create(array_merge([
        'type' => BuyXGetY::class,
        'name' => 'Buy one get one',
        'starts_at' => now()->subDay(),
        'data' => array_merge([
            'min_qty' => 1,
            'reward_qty' => 1,
            'automatically_add_rewards' => false,
        ], $discountData),
    ], $discountAttributes));

    $discount->channels()->attach([
        $channel->id => ['enabled' => true, 'starts_at' => now()->subDay()],
    ]);

    $discount->customerGroups()->attach([
        $customerGroup->id => ['enabled' => true, 'starts_at' => now()->subDay()],
    ]);

    $discount->discountables()->create([
        'discountable_type' => $conditionProduct->getMorphClass(),
        'discountable_id' => $conditionProduct->id,
        'type' => 'condition',
    ]);

    $discount->discountables()->create([
        'discountable_type' => $rewardProduct->getMorphClass(),
        'discountable_id' => $rewardProduct->id,
        'type' => 'reward',
    ]);

    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
        'coupon_code' => null,
    ]);

    // £10 of the condition product and £10 of the reward product: £20 total.
    $cart->lines()->create([
        'purchasable_type' => $condition->getMorphClass(),
        'purchasable_id' => $condition->id,
        'quantity' => 1,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $reward->getMorphClass(),
        'purchasable_id' => $reward->id,
        'quantity' => 1,
    ]);

    return $cart->refresh();
}

test('is not applied when the cart is below the minimum spend', function () {
    // £50 minimum against a £20 cart.
    $cart = buyXGetYConditionCart([
        'min_prices' => ['GBP' => 5000],
    ]);

    $cart->calculate();

    expect($cart->discountTotal->value)->toEqual(0);
});

test('is applied when the cart meets the minimum spend', function () {
    // £10 minimum against a £20 cart.
    $cart = buyXGetYConditionCart([
        'min_prices' => ['GBP' => 1000],
    ]);

    $cart->calculate();

    expect($cart->discountTotal->value)->toBeGreaterThan(0);
});

test('is not applied when the cart customer is not on the discount', function () {
    $cart = buyXGetYConditionCart([]);

    // Restrict the discount to a customer this cart does not belong to.
    $discount = Discount::first();
    $discount->customers()->attach(Customer::factory()->create()->id);

    $cart->refresh()->calculate();

    expect($cart->discountTotal->value)->toEqual(0);
});
