<?php

namespace GetCandy\Discounts\Tests\Unit\Models;

use GetCandy\Discounts\Models\Discount;
use GetCandy\Discounts\Models\DiscountCondition;
use GetCandy\Discounts\Models\DiscountReward;
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
class DiscountTest extends TestCase
{
    use RefreshDatabase, TestUtils;

    /** @test */
    public function zones_method_uses_shipping_zone_resolver()
    {
        Discount::factory(100)->has(
            DiscountCondition::factory()->count(5),
            'conditions'
        )->has(
            DiscountReward::factory()->count(5),
            'rewards'
        )->create();

        $cart = $this->createCart();

        dd($cart);
        dd(1);
    }
}
