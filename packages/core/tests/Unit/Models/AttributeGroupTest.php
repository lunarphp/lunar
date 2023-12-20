<?php

namespace Lunar\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;
use Lunar\Tests\TestCase;

class AttributeGroupTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_make_a_attribute_group()
    {
        $attributeGroup = AttributeGroup::factory()->create([
            'attributable_type' => 'product_type',
            'name' => [
                'en' => 'SEO',
            ],
            'handle' => 'seo',
        ]);

        $this->assertEquals('SEO', $attributeGroup->name->get('en'));
        $this->assertEquals('seo', $attributeGroup->handle);
        $this->assertIsInt($attributeGroup->position);
        $this->assertGreaterThan(0, $attributeGroup->position);
    }

    /** @test */
    public function can_get_associated_attributes()
    {
        $attributeGroup = AttributeGroup::factory()->create([
            'attributable_type' => 'product_type',
            'name' => [
                'en' => 'SEO',
            ],
            'handle' => 'seo',
        ]);

        $this->assertCount(0, $attributeGroup->attributes);

        $attributeGroup->attributes()->create(
            Attribute::factory()->make()->toArray()
        );

        $this->assertCount(1, $attributeGroup->refresh()->attributes);
    }
}
