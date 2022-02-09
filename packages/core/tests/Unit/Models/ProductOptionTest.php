<?php

namespace GetCandy\Tests\Unit\Models;

use GetCandy\Models\ProductOption;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
        $expected = config('scout.prefix').'product_options_en';

        $this->assertEquals($expected, (new ProductOption)->searchableAs());
    }
}
