<?php

namespace GetCandy\Discounts\Tests\Unit\Models;

use GetCandy\Discounts\Models\Discount;
use GetCandy\Discounts\Models\DiscountCondition;
use GetCandy\Discounts\Models\DiscountReward;
use GetCandy\Discounts\Tests\TestCase;
use GetCandy\Discounts\Tests\TestUtils;
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
        $this->assertTrue(true);
//         dd(1);
//         Discount::factory(100)->has(
//             DiscountCondition::factory()->count(5),
//             'conditions'
//         )->has(
//             DiscountReward::factory()->count(5),
//             'rewards'
//         )->create();
//
//         $cart = $this->createCart();
//
//         dd($cart);
//         dd(1);
    }
}
