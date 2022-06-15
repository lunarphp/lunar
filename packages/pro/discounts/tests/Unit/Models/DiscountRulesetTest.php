<?php

namespace GetCandy\Discounts\Tests\Unit\Models;

use GetCandy\Discounts\Models\Discount;
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
