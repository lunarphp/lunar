<?php

namespace GetCandy\Tests\Unit\Models;

use GetCandy\Models\Attribute;
use GetCandy\Models\AttributeGroup;
use GetCandy\Models\ProductType;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTypeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_make_a_product_type()
    {
        $productType = ProductType::factory()
            ->has(
                Attribute::factory()->for(AttributeGroup::factory())->count(1),
                'mappedAttributes',
            )
            ->create([
                'name' => 'Bob',
            ]);

        $this->assertEquals('Bob', $productType->name);
    }
}
