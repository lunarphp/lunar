<?php

namespace GetCandy\Discounts\Tests\Unit\Rules;

use GetCandy\Discounts\Drivers\Rules\Coupon;
use GetCandy\Discounts\Models\Discount;
use GetCandy\Discounts\Models\DiscountRule;
use GetCandy\Discounts\Models\DiscountRuleset;
use GetCandy\Discounts\Tests\TestCase;
use GetCandy\Discounts\Tests\TestUtils;
use GetCandy\Models\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group getcandy.discounts.drivers
 */
class CartTotalTest extends TestCase
{
    use RefreshDatabase, TestUtils;

    /** @test */
    public function can_check_rule()
    {
        $gbp = Currency::factory()->create([
            'default' => true,
            'code' => 'GBP',
        ]);

        $usd = Currency::factory()->create([
            'default' => false,
            'code' => 'USD',
        ]);

        $discount = Discount::factory()->create();

        $ruleset = DiscountRuleset::factory()->create([
            'discount_id' => $discount,
            'criteria' => 'all',
        ]);

        $rule = DiscountRule::factory()->create([
           'discount_ruleset_id' => $ruleset->id,
           'driver' => 'cart_total',
           'data' => [
               'totals' => [
                    'GBP' => 100,
                    'USD' => 200,
                ],
           ]
        ]);

        $driver = $rule->driver();

        $cart = $this->createCart(
            currency: $gbp,
            price: 50,
            lineCount: 1,
        );

        $this->assertFalse($driver->check($cart));

        $cart = $this->createCart(
            currency: $gbp,
            price: 100,
            lineCount: 1,
        );

        $this->assertTrue($driver->check($cart));

        $cart = $this->createCart(
            currency: $gbp,
            price: 200,
            lineCount: 1,
        );

        $this->assertTrue($driver->check($cart));

        $cart = $this->createCart(
            price: 1000,
            lineCount: 1,
        );

        $this->assertFalse($driver->check($cart));

        $cart = $this->createCart(
            currency: $usd,
            price: 100,
            lineCount: 1,
        );

        $this->assertFalse($driver->check($cart));

        $cart = $this->createCart(
            currency: $usd,
            price: 200,
            lineCount: 1,
        );

        $this->assertTrue($driver->check($cart));
    }
}
