<?php

namespace GetCandy\Tests\Unit\Models;

use GetCandy\Models\Attribute;
use GetCandy\Models\AttributeGroup;
use GetCandy\Models\Product;
use GetCandy\Models\ProductOption;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttributeGroupTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_make_a_attribute_group()
    {
        $attributeGroup = AttributeGroup::factory()->create([
            'attributable_type' => 'product_type',
            'name'              => [
                'en' => 'SEO',
            ],
            'handle'   => 'seo',
            'position' => 5,
        ]);

        $this->assertEquals('SEO', $attributeGroup->name->get('en'));
        $this->assertEquals('seo', $attributeGroup->handle);
        $this->assertEquals(5, $attributeGroup->position);
    }

    /** @test */
    public function can_get_associated_attributes()
    {
        $attributeGroup = AttributeGroup::factory()->create([
            'attributable_type' => 'product_type',
            'name'              => [
                'en' => 'SEO',
            ],
            'handle'   => 'seo',
            'position' => 5,
        ]);

        $this->assertCount(0, $attributeGroup->attributes);

        $attributeGroup->attributes()->create(
            Attribute::factory()->make()->toArray()
        );

        $this->assertCount(1, $attributeGroup->refresh()->attributes);
    }

    /** @test */
    public function can_make_dynamic_attribute_group_for_product_options()
    {
        $attributeGroup = AttributeGroup::factory()->create([
            'type' => 'model',
            'attributable_type' => Product::class,
            'source' => ProductOption::class,
            'name' => ['en' => 'Product Options'],
            'handle' => 'product_options',
            'position' => 2,
        ]);

        $this->assertEquals('Product Options', $attributeGroup->name->get('en'));
        $this->assertEquals('product_options', $attributeGroup->handle);
        $this->assertEquals(ProductOption::class, $attributeGroup->source);
        $this->assertEquals(2, $attributeGroup->position);
    }
}
