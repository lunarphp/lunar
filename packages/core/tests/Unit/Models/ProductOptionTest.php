<?php

namespace Lunar\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\ProductOption;
use Lunar\Tests\TestCase;

/**
 * @group products
 */
class ProductOptionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * */
    public function takes_scout_prefix_into_account()
    {
        $expected = config('scout.prefix').'product_options';

        $this->assertEquals($expected, (new ProductOption)->searchableAs());
    }
}
