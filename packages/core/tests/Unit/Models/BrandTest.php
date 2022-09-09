<?php

namespace GetCandy\Tests\Unit\Models;

use GetCandy\Models\Brand;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BrandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_make_a_brand()
    {
        $brand = Brand::factory()->create([
            'name' => 'Test Brand',
        ]);
        $this->assertEquals('Test Brand', $brand->name);
    }
}
