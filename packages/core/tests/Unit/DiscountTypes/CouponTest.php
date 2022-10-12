<?php

namespace Lunar\Tests\Unit\DiscountTypes;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\DiscountTypes\Coupon;
use Lunar\Managers\CartManager;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\Discount;
use Lunar\Models\Price;
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
        $purchasableB = ProductVariant::factory()->create();
        $purchasableC = ProductVariant::factory()->create();

        Price::factory()->create([
            'price' => 450, // £5
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
            'price' => 325, // £3.25
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

        Price::factory()->create([
            'price' => 325, // £3.25
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasableC),
            'priceable_id' => $purchasableC->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasableC),
            'purchasable_id' => $purchasableC->id,
            'quantity' => 1,
        ]);

        $manager = new CartManager($cart);

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

        $cart = $manager->getCart();

        $this->assertEquals(1000, $cart->discountTotal->value);
        $this->assertEquals(120, $cart->total->value);
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

        $manager = new CartManager($cart);

        $this->assertNull($cart->total);
        $this->assertNull($cart->taxTotal);
        $this->assertNull($cart->subTotal);

        $cart = $manager->getCart();

        $this->assertEquals(100, $cart->discountTotal->value);
        $this->assertEquals(180, $cart->taxTotal->value);
        $this->assertEquals(1080, $cart->total->value);
    }
//
//     /**
//      * @test
//      */
//     public function can_apply_fixed_amount_discount()
//     {
//         $currency = Currency::factory()->create([
//             'code' => 'GBP',
//         ]);
//
//         $cart = Cart::factory()->create([
//             'currency_id' => $currency->id,
//             'meta' => [
//                 'coupon' => '10FIXEDOFF',
//             ],
//         ]);
//
//         $purchasable = ProductVariant::factory()->create();
//
//         Price::factory()->create([
//             'price' => 2000,
//             'tier' => 1,
//             'currency_id' => $currency->id,
//             'priceable_type' => get_class($purchasable),
//             'priceable_id' => $purchasable->id,
//         ]);
//
//         $cart->lines()->create([
//             'purchasable_type' => get_class($purchasable),
//             'purchasable_id' => $purchasable->id,
//             'quantity' => 1,
//         ]);
//
//         $discount = Discount::factory()->create([
//             'type' => Coupon::class,
//             'name' => 'Test Coupon',
//             'data' => [
//                 'coupon' => '10FIXEDOFF',
//                 'fixed_value' => true,
//                 'fixed_values' => [
//                     $currency->code => 10,
//                 ],
//             ],
//         ]);
//
//         $manager = new CartManager($cart);
//
//         $this->assertNull($cart->total);
//         $this->assertNull($cart->taxTotal);
//         $this->assertNull($cart->subTotal);
//
//         $cart = $manager->getCart();
//
//         $this->assertEquals(1000, $cart->discountTotal->value);
//         $this->assertEquals(200, $cart->taxTotal->value);
//         $this->assertEquals(1200, $cart->total->value);
//     }
//
//     /**
//      * @test
//      */
//     public function can_apply_fixed_amount_to_multiple_lines()
//     {
//         $currency = Currency::factory()->create();
//
//         $cart = Cart::factory()->create([
//             'currency_id' => $currency->id,
//             'meta' => [
//                 'coupon' => '10FIXEDOFF',
//             ],
//         ]);
//
//         for ($i = 0; $i < 3; $i++) {
//             $purchasable = ProductVariant::factory()->create();
//
//             Price::factory()->create([
//                 'price' => 100,
//                 'tier' => 1,
//                 'currency_id' => $currency->id,
//                 'priceable_type' => get_class($purchasable),
//                 'priceable_id' => $purchasable->id,
//             ]);
//
//             $cart->lines()->create([
//                 'purchasable_type' => get_class($purchasable),
//                 'purchasable_id' => $purchasable->id,
//                 'quantity' => 1,
//             ]);
//         }
//
//         $discount = Discount::factory()->create([
//             'type' => Coupon::class,
//             'name' => 'Test Coupon',
//             'data' => [
//                 'coupon' => '10FIXEDOFF',
//                 'fixed_value' => true,
//                 'fixed_values' => [
//                     $currency->code => 10,
//                 ],
//             ],
//         ]);
//
//         $manager = new CartManager($cart);
//
//         $this->assertNull($cart->total);
//         $this->assertNull($cart->taxTotal);
//         $this->assertNull($cart->subTotal);
//
//         $cart = $manager->getCart();
//
//         $this->assertEquals(999, $cart->discountTotal->value);
//     }
}
