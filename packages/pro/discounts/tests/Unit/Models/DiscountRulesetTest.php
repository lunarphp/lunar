<?php

namespace GetCandy\Discounts\Tests\Unit\Models;

use GetCandy\Discounts\Models\Discount;
use GetCandy\Discounts\Models\DiscountCondition;
use GetCandy\Discounts\Models\DiscountReward;
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
 * @group getcandy.discounts
 */
class DiscountRulesetTest extends TestCase
{
    use RefreshDatabase, TestUtils;

    /** @test */
    public function can_create_discount_ruleset()
    {
        $discount = Discount::factory()->create();

        $ruleset = DiscountRuleset::factory()->create([
            'discount_id' => $discount,
            'criteria' => 'all',
        ]);

        $this->assertDatabaseHas((new DiscountRuleset)->getTable(), [
            'discount_id' => $discount->id,
            'id' => $ruleset->id,
            'criteria' => 'all',
        ]);

        $this->assertInstanceOf(Discount::class, $ruleset->discount);
    }
}
