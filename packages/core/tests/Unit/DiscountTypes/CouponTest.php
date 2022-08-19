<?php

namespace GetCandy\Tests\Unit\DiscountTypes;

use GetCandy\DiscountTypes\Coupon;
use GetCandy\Managers\CartManager;
use GetCandy\Models\Cart;
use GetCandy\Models\Currency;
use GetCandy\Models\Discount;
use GetCandy\Models\Price;
use GetCandy\Models\ProductVariant;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group getcandy.discounts
 * @group getcandy.discounts.coupon
 */
class CouponTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_apply_percentage_discount()
    {
        $currency = Currency::factory()->create([
            'code' => 'GBP',
        ]);

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
            'meta' => [
                'coupon' => '10PERCENTOFF',
            ],
        ]);

        $purchasable = ProductVariant::factory()->create();

        Price::factory()->create([
            'price'          => 1000,
            'tier'           => 1,
            'currency_id'    => $currency->id,
            'priceable_type' => get_class($purchasable),
            'priceable_id'   => $purchasable->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasable),
            'purchasable_id'   => $purchasable->id,
            'quantity'         => 1,
        ]);

        $discount = Discount::factory()->create([
            'type' => Coupon::class,
            'name' => 'Test Coupon',
            'data' => [
                'coupon' => '10PERCENTOFF',
                'percentage' => 10,
                'fixed_value' => false,
            ],
        ]);

        $manager = new CartManager($cart);

        $this->assertNull($cart->total);
        $this->assertNull($cart->taxTotal);
        $this->assertNull($cart->subTotal);

        $cart = $manager->getCart();

        $this->assertEquals(100, $cart->discountTotal->value);
        $this->assertEquals(18, $cart->taxTotal->value);
        $this->assertEquals(108, $cart->total->value);
    }

    /**
     * @test
     * @group this
     */
    public function can_apply_fixed_amount_discount()
    {
        $currency = Currency::factory()->create([
            'code' => 'GBP',
        ]);

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
            'meta' => [
                'coupon' => '10FIXEDOFF',
            ],
        ]);

        $purchasable = ProductVariant::factory()->create();

        Price::factory()->create([
            'price'          => 2000,
            'tier'           => 1,
            'currency_id'    => $currency->id,
            'priceable_type' => get_class($purchasable),
            'priceable_id'   => $purchasable->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasable),
            'purchasable_id'   => $purchasable->id,
            'quantity'         => 1,
        ]);

        $discount = Discount::factory()->create([
            'type' => Coupon::class,
            'name' => 'Test Coupon',
            'data' => [
                'coupon' => '10FIXEDOFF',
                'fixed_value' => true,
                'fixed_values' => [
                    $currency->code => 10,
                ],
            ],
        ]);

        $manager = new CartManager($cart);

        $this->assertNull($cart->total);
        $this->assertNull($cart->taxTotal);
        $this->assertNull($cart->subTotal);

        $cart = $manager->getCart();

        $this->assertEquals(1000, $cart->discountTotal->value);
        $this->assertEquals(200, $cart->taxTotal->value);
        $this->assertEquals(1200, $cart->total->value);
    }

    /**
     * @test
     */
    public function can_apply_fixed_amount_to_multiple_lines()
    {
        $currency = Currency::factory()->create();

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
            'meta' => [
                'coupon' => '10FIXEDOFF',
            ],
        ]);

        for ($i = 0; $i < 3; $i++) {
            $purchasable = ProductVariant::factory()->create();

            Price::factory()->create([
                'price'          => 100,
                'tier'           => 1,
                'currency_id'    => $currency->id,
                'priceable_type' => get_class($purchasable),
                'priceable_id'   => $purchasable->id,
            ]);

            $cart->lines()->create([
                'purchasable_type' => get_class($purchasable),
                'purchasable_id'   => $purchasable->id,
                'quantity'         => 1,
            ]);
        }

        $discount = Discount::factory()->create([
            'type' => Coupon::class,
            'name' => 'Test Coupon',
            'data' => [
                'coupon' => '10FIXEDOFF',
                'fixed_value' => true,
                'fixed_values' => [
                    $currency->code => 10,
                ],
            ],
        ]);

        $manager = new CartManager($cart);

        $this->assertNull($cart->total);
        $this->assertNull($cart->taxTotal);
        $this->assertNull($cart->subTotal);

        $cart = $manager->getCart();

        $this->assertEquals(999, $cart->discountTotal->value);
    }
}
