<?php

namespace GetCandy\Discounts\Tests\Unit\Models;

use GetCandy\Discounts\Facades\Discounts;
use GetCandy\Discounts\Tests\TestCase;
use GetCandy\Discounts\Tests\TestUtils;
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
