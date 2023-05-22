<?php

namespace Lunar\Tests\Unit\DiscountTypes;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
use Lunar\Tests\TestCase;

/**
 * @group lunar.discounts
 * @group lunar.discounts.products
 */
class BuyXGetYTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_determine_correct_reward_qty()
    {
        $driver = new BuyXGetY;

        $checks = [
            [
                'linesQuantity' => 1,
                'minQty' => 1,
                'rewardQty' => 1,
                'expected' => 1,
            ],
            [
                'linesQuantity' => 2,
                'minQty' => 1,
                'rewardQty' => 1,
                'expected' => 2,
            ],
            [
                'linesQuantity' => 2,
                'minQty' => 2,
                'rewardQty' => 1,
                'expected' => 1,
            ],
            [
                'linesQuantity' => 10,
                'minQty' => 10,
                'rewardQty' => 1,
                'expected' => 1,
            ],
            [
                'linesQuantity' => 10,
                'minQty' => 1,
                'rewardQty' => 1,
                'expected' => 10,
            ],
            [
                'linesQuantity' => 10,
                'minQty' => 1,
                'rewardQty' => 1,
                'maxRewardQty' => 5,
                'expected' => 5,
            ],
        ];

        foreach ($checks as $check) {
            $this->assertEquals(
                $check['expected'],
                $driver->getRewardQuantity(
                    $check['linesQuantity'],
                    $check['minQty'],
                    $check['rewardQty'],
                    $check['maxRewardQty'] ?? null
                )
            );
        }
    }

    /** @test */
    public function can_discount_eligible_product()
    {
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
            'tier' => 1,
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
            'tier' => 1,
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
            'type' => BuyXGetY::class,
            'name' => 'Test Product Discount',
            'data' => [
                'min_qty' => 1,
                'reward_qty' => 1,
            ],
        ]);

        $discount->purchasableConditions()->create([
            'purchasable_type' => Product::class,
            'purchasable_id' => $productA->id,
        ]);

        $discount->purchasableRewards()->create([
            'purchasable_type' => Product::class,
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

        $this->assertEquals(1000, $purchasableBCartLine->discountTotal->value);
    }

    /**
     * @test
     *
     * @group thisthis
     */
    public function can_discount_eligible_products()
    {
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
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableA),
            'priceable_id' => $purchasableA->id,
        ]);

        Price::factory()->create([
            'price' => 1000, // £10
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableB),
            'priceable_id' => $purchasableB->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasableA),
            'purchasable_id' => $purchasableA->id,
            'quantity' => 1,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasableB),
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
            'purchasable_type' => Product::class,
            'purchasable_id' => $productA->id,
        ]);

        $discount->purchasableRewards()->create([
            'purchasable_type' => Product::class,
            'purchasable_id' => $productB->id,
            'type' => 'reward',
        ]);

        $cart = $cart->calculate();

        $this->assertEquals(1200, $cart->total->value);
        $this->assertCount(1, $cart->freeItems);
    }

    /**
     * @test
     */
    public function can_discount_purchasable_with_priority()
    {
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
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableA),
            'priceable_id' => $purchasableA->id,
        ]);

        Price::factory()->create([
            'price' => 1000, // £10
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableB),
            'priceable_id' => $purchasableB->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasableA),
            'purchasable_id' => $purchasableA->id,
            'quantity' => 1,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasableB),
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
            'purchasable_type' => Product::class,
            'purchasable_id' => $productA->id,
        ]);

        $discount->purchasableRewards()->create([
            'purchasable_type' => Product::class,
            'purchasable_id' => $productB->id,
            'type' => 'reward',
        ]);

        $cart = $cart->calculate();

        $this->assertEquals(1200, $cart->total->value);
        $this->assertCount(1, $cart->freeItems);
    }

    /**
     * @test
     * @group flub
     *
     * Scenario
     * ----------------------------------------------------
     *
     * Product A costs 10.00 before tax
     * Product B costs 5.00 before tax
     * Discount A is a BuyXGetY. Reward: Product B Condition: 2 x Product A
     * Discount B is an Amount off for 10%
     *
     * Cart:
     * 2 x Product A = (10.00 x 2) - 10% = 18.00 excl tax
     * 2 x Product B = ((5.00 x 2) - 5.00) - 10% = 4.50 excl tax
     * Sub total: 22.50
     * Discount total: 7.50
     * Tax total: 4.50
     * Total: 27.00
     */
    public function can_apply_multiple_different_discounts()
    {
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
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableA),
            'priceable_id' => $purchasableA->id,
        ]);

        Price::factory()->create([
            'price' => 500, // £5
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableB),
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
            'purchasable_type' => get_class($purchasableA),
            'purchasable_id' => $purchasableA->id,
            'quantity' => 2,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasableB),
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
            'priority' => 1,
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
            'purchasable_type' => Product::class,
            'purchasable_id' => $productA->id,
        ]);

        $discountA->purchasableRewards()->create([
            'purchasable_type' => Product::class,
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

        $this->assertEquals(2000, $lineA->subTotal->value);
        $this->assertEquals(1800, $lineA->subTotalDiscounted->value);
        $this->assertEquals(200, $lineA->discountTotal->value);

        $this->assertEquals(1000, $lineB->subTotal->value);
        $this->assertEquals(450, $lineB->subTotalDiscounted->value);
        $this->assertEquals(550, $lineB->discountTotal->value);

        $this->assertEquals(750, $cart->discountTotal->value);
        $this->assertCount(2, $cart->discountBreakdown);
    }

    /**
     * @test
     */
    public function can_supplement_correct_quantities()
    {
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
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableA),
            'priceable_id' => $purchasableA->id,
        ]);

        Price::factory()->create([
            'price' => 2280, // $22.80
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableB),
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
            'purchasable_type' => Product::class,
            'purchasable_id' => $productA->id,
        ]);

        $discount->purchasableRewards()->create([
            'purchasable_type' => Product::class,
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
                'purchasable_type' => get_class($purchasableA),
                'purchasable_id' => $purchasableA->id,
                'quantity' => $line['condition_quantity'],
            ]);

            $cart->lines()->create([
                'purchasable_type' => get_class($purchasableB),
                'purchasable_id' => $purchasableB->id,
                'quantity' => $line['reward_quantity'],
            ]);

            $cart = $cart->calculate();

            $discountedLine = $cart->lines->first(function ($line) use ($purchasableB) {
                return $line->purchasable_id == $purchasableB->id;
            });
            $this->assertEquals($line['expected_discount'], $discountedLine->discountTotal->value);
            $this->assertEquals($line['expected_subtotal'], $discountedLine->subTotalDiscounted->value);
        }
    }

    /**
     * @test
     *
     * Scenario
     * ----------------------------------------------------
     *
     * Product A costs 10.00 before tax
     * Product B costs 5.00 before tax
     * Product C costs 2.00 before tax
     * Discount A is a BuyXGetY. Reward: Product C Condition: 1 x Product A, 1 x Product B
     *
     * Cart:
     * 1 x Product A = 10.00 excl tax
     * 1 x Product B = 5.00 excl tax
     * 1 x Product C = 0.00 excl tax
     * Sub total: 17.00
     * Discount total: 2.00
     * Tax total: 3.00
     * Total: 15.00
     */
    public function can_count_condition_qty_in_discount_breakdown()
    {
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
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableA),
            'priceable_id' => $purchasableA->id,
        ]);

        Price::factory()->create([
            'price' => 500, // £5
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableB),
            'priceable_id' => $purchasableB->id,
        ]);

        Price::factory()->create([
            'price' => 200, // £2
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableC),
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
            'purchasable_type' => get_class($purchasableA),
            'purchasable_id' => $purchasableA->id,
            'quantity' => 1,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasableB),
            'purchasable_id' => $purchasableB->id,
            'quantity' => 1,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasableC),
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
            'purchasable_type' => Product::class,
            'purchasable_id' => $productA->id,
        ]);

        $discountA->purchasableConditions()->create([
            'purchasable_type' => Product::class,
            'purchasable_id' => $productB->id,
        ]);

        $discountA->purchasableConditions()->create([
            'purchasable_type' => Product::class,
            'purchasable_id' => $productC->id,
        ]);

        $discountA->purchasableRewards()->create([
            'purchasable_type' => Product::class,
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

        $this->assertEquals(1000, $lineA->subTotal->value);
        $this->assertEquals(0, $lineA->discountTotal->value);

        $this->assertEquals(500, $lineB->subTotal->value);
        $this->assertEquals(0, $lineB->discountTotal->value);

        $this->assertEquals(200, $lineC->subTotal->value);
        $this->assertEquals(0, $lineC->subTotalDiscounted->value);
        $this->assertEquals(200, $lineC->discountTotal->value);

        $this->assertEquals(1700, $cart->subTotal->value);
        $this->assertEquals(200, $cart->discountTotal->value);
        $this->assertEquals(1800, $cart->total->value);
        $this->assertCount(1, $cart->discountBreakdown);
        $this->assertCount(3, $cart->discountBreakdown->first()->lines);
    }
}
