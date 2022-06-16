<?php

namespace GetCandy\Discounts\Tests\Unit\Models;

use GetCandy\Discounts\Facades\Discounts;
use GetCandy\Discounts\Managers\DiscountRulesetManager;
use GetCandy\Discounts\Models\Discount;
use GetCandy\Discounts\Models\DiscountCondition;
use GetCandy\Discounts\Models\DiscountReward;
use GetCandy\Discounts\Models\DiscountRule;
use GetCandy\Discounts\Models\DiscountRuleset;
use GetCandy\Discounts\Tests\TestCase;
use GetCandy\Discounts\Tests\TestUtils;
use GetCandy\Models\CartAddress;
use GetCandy\Models\Country;
use GetCandy\Models\Currency;
use GetCandy\Models\TaxClass;
use GetCandy\Shipping\Facades\Shipping;
use GetCandy\Shipping\Models\ShippingMethod;
use GetCandy\Shipping\Models\ShippingZone;
use GetCandy\Shipping\Resolvers\ShippingZoneResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group getcandy.discounts.managers
 */
class DiscountRulesetManagerTest extends TestCase
{
    use RefreshDatabase, TestUtils;

    /** @test */
    public function can_handle_match_all_rules()
    {
        $currency = Currency::factory()->create([
            'code' => 'GBP',
        ]);

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

        $rule = DiscountRule::factory()->create([
           'discount_ruleset_id' => $ruleset->id,
           'driver' => 'cart_total',
           'data' => [
               'totals' => [
                   'GBP' => 100,
               ],
           ]
        ]);

        $manager = new DiscountRulesetManager($ruleset);

        $cart = $this->createcart(
            currency: $currency,
            price: 100,
            lineCount: 1,
            meta: [
                'coupon' => '10OFF',
            ]
        );

        $this->assertTrue(
            $manager->check($cart)
        );

        $this->assertCount(1, Discounts::getApplied());
    }

    /** @test */
    public function can_handle_match_any_rules()
    {
        $currency = Currency::factory()->create([
            'code' => 'GBP',
        ]);

        $discount = Discount::factory()->create();

        $ruleset = DiscountRuleset::factory()->create([
            'discount_id' => $discount,
            'criteria' => 'any',
        ]);

        $rule = DiscountRule::factory()->create([
           'discount_ruleset_id' => $ruleset->id,
           'driver' => 'coupon',
           'data' => [
               'coupon' => '10OFF',
           ]
        ]);

        $rule = DiscountRule::factory()->create([
           'discount_ruleset_id' => $ruleset->id,
           'driver' => 'cart_total',
           'data' => [
               'totals' => [
                   'GBP' => 100,
               ],
           ]
        ]);

        $manager = Discounts::ruleset($ruleset);

        $cart = $this->createcart(
            currency: $currency,
            price: 100,
            lineCount: 1
        );

        $this->assertTrue(
            $manager->check($cart)
        );
    }
}
