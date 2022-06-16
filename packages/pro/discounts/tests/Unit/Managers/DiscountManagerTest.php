<?php

namespace GetCandy\Discounts\Tests\Unit\Models;

use GetCandy\Discounts\Facades\Discounts;
use GetCandy\Discounts\Managers\DiscountRulesetManager;
use GetCandy\Discounts\Models\Discount;
use GetCandy\Discounts\Models\DiscountRule;
use GetCandy\Discounts\Models\DiscountRuleset;
use GetCandy\Discounts\Tests\TestCase;
use GetCandy\Discounts\Tests\TestUtils;
use GetCandy\Models\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group getcandy.discounts.managers
 */
class DiscountManagerTest extends TestCase
{
    use RefreshDatabase, TestUtils;

    /** @test */
    public function can_run()
    {
        // Get all the discounts
        $cart = $this->createcart();

        Discounts::run($cart);
    }
}
