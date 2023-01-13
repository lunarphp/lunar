<?php

namespace Lunar\Tests\Unit\DiscountTypes;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $productC = Product::factory()->create();

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
}
