<?php

namespace Lunar\Tests\Unit\DiscountTypes;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\DiscountTypes\Coupon;
use Lunar\Managers\CartManager;
use Lunar\Models\Brand;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\Discount;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Lunar\Tests\TestCase;

/**
 * @group getcandy.discounts
 * @group getcandy.discounts.coupon
 */
class CouponTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function will_only_apply_to_lines_with_correct_brand()
    {
        $currency = Currency::factory()->create([
            'code' => 'GBP',
        ]);

        $cart = Cart::factory()->create([
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
            'type' => Coupon::class,
            'name' => 'Test Coupon',
            'data' => [
                'coupon' => '10OFF',
                'fixed_value' => false,
                'percentage' => 10,
            ],
        ]);

        $discount->brands()->sync([$brandA->id]);

        $cart = $cart->calculate();

        $this->assertEquals(100, $cart->discountTotal->value);
        $this->assertEquals(2100, $cart->total->value);
    }

    /**
     * @test
     * @group thisdiscount
     */
    public function can_apply_fixed_amount_discount()
    {
        $currency = Currency::factory()->create([
            'code' => 'GBP',
        ]);

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
            'coupon_code' => '10OFF',
        ]);

        $purchasableA = ProductVariant::factory()->create();

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
            'quantity' => 2,
        ]);

        Discount::factory()->create([
            'type' => Coupon::class,
            'name' => 'Test Coupon',
            'data' => [
                'coupon' => '10OFF',
                'fixed_value' => true,
                'fixed_values' => [
                    'GBP' => 10,
                ],
            ],
        ]);

        $cart = $cart->calculate();

        $this->assertEquals(1000, $cart->discountTotal->value);
        $this->assertEquals(1400, $cart->total->value);
        $this->assertEquals(400, $cart->taxTotal->value);
        $this->assertCount(1, $cart->discounts);
    }

    /** @test */
    public function can_apply_percentage_discount()
    {
        $currency = Currency::factory()->create([
            'code' => 'GBP',
        ]);

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
            'coupon_code' => '10PERCENTOFF',
        ]);

        $purchasable = ProductVariant::factory()->create();

        Price::factory()->create([
            'price' => 1000,
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasable),
            'priceable_id' => $purchasable->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasable),
            'purchasable_id' => $purchasable->id,
            'quantity' => 1,
        ]);

        Discount::factory()->create([
            'type' => Coupon::class,
            'name' => 'Test Coupon',
            'data' => [
                'coupon' => '10PERCENTOFF',
                'percentage' => 10,
                'fixed_value' => false,
            ],
        ]);

        $this->assertNull($cart->total);
        $this->assertNull($cart->taxTotal);
        $this->assertNull($cart->subTotal);

        $cart = $cart->calculate();

        $this->assertEquals(100, $cart->discountTotal->value);
        $this->assertEquals(200, $cart->taxTotal->value);
        $this->assertEquals(1100, $cart->total->value);
    }
}
