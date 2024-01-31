<?php

namespace Lunar\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\FieldTypes\Text;
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;
use Lunar\Models\ProductType;
use Lunar\Tests\TestCase;

class ProductTypeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_make_a_product_type()
    {
        $productType = ProductType::factory()->create();

        $this->assertModelExists($productType);
        $this->assertInstanceOf(Text::class, $productType->attribute_data['description']);
    }

    /** @test */
    public function product_type_can_have_mapped_attributes()
    {   
        $attributes = Attribute::factory()
            ->has(AttributeGroup::factory()->state([
                'attributable_type' => ProductType::class
            ]), 'attributeGroup')
            ->count(1)
            ->create([
                'attribute_type' => ProductType::class
            ]);

        $productType = ProductType::factory()->create();
        $productType->mappedAttributes()->saveMany($attributes);

        $this->assertEquals(
            $productType->mappedAttributes->pluck('id'), 
            $attributes->pluck('id')
        );
    }

    /** @test */
    public function product_type_can_have_attributables()
    {   
        $attributes = Attribute::factory()
            ->has(AttributeGroup::factory(), 'attributeGroup')
            ->count(1)
            ->create();

        $productType = ProductType::factory()->create();
        $productType->attributables()->sync($attributes);

        $this->assertEquals(
            $productType->attributables->pluck('id'), 
            $attributes->pluck('id')
        );
    }
}
