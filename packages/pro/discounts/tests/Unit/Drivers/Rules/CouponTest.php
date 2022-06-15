<?php

namespace GetCandy\Discounts\Tests\Unit\Rules;

use GetCandy\Discounts\Drivers\Rules\Coupon;
use GetCandy\Discounts\Models\Discount;
use GetCandy\Discounts\Models\DiscountRule;
use GetCandy\Discounts\Models\DiscountRuleset;
use GetCandy\Discounts\Tests\TestCase;
use GetCandy\Discounts\Tests\TestUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group getcandy.discounts
 */
class DiscountRulesetTest extends TestCase
{
    use RefreshDatabase, TestUtils;

    /** @test */
    public function can_check_rule()
    {
        $discount = Discount::factory()->create();

        $ruleset = DiscountRuleset::factory()->create([
            'discount_id' => $discount,
            'criteria' => 'all',
        ]);

        $rule = DiscountRule::factory()->create([
           'discount_ruleset_id' => $ruleset->id,
           'driver' => 'coupon',
           'data' => [
               'coupon' => '10OFF',
           ]
        ]);

        $coupon = $rule->driver();

        $cart = $this->createCart(
            meta: [
                'coupon' => '10OFF',
            ]
        );

        $this->assertInstanceOf(Coupon::class, $coupon);

        $this->assertTrue($coupon->check($cart));

        $cart->update([
            'meta' => [
                'coupon' => '10off',
            ],
        ]);

        $this->assertTrue($coupon->check($cart));

        $cart->update([
            'meta' => [
                'coupon' => '20OFF',
            ],
        ]);

        $this->assertFalse($coupon->check($cart));

        $cart->update([
            'meta' => [
                'coupon' => null,
            ],
        ]);

        $this->assertFalse($coupon->check($cart));

        $rule->update([
            'data' => [
                'coupon' => null,
            ],
        ]);

        $this->assertFalse($rule->driver()->check($cart));
    }
}
