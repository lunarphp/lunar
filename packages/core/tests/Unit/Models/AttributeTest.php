<?php

namespace Lunar\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;
use Lunar\Tests\TestCase;

class AttributeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_make_a_attribute()
    {
        $options = [
            'Red',
            'Blue',
            'Green',
        ];

        $attribute = Attribute::factory()
            ->for(AttributeGroup::factory())
            ->create([
                'position' => 4,
                'name' => [
                    'en' => 'Meta Description',
                ],
                'handle' => 'meta_description',
                'section' => 'product_variant',
                'type' => \Lunar\FieldTypes\Text::class,
                'required' => false,
                'default_value' => '',
                'configuration' => [
                    'options' => $options,
                ],
                'system' => true,
            ]);

        $this->assertEquals('Meta Description', $attribute->name->get('en'));
        $this->assertEquals('meta_description', $attribute->handle);
        $this->assertEquals(\Lunar\FieldTypes\Text::class, $attribute->type);
        $this->assertTrue($attribute->system);
        $this->assertEquals(4, $attribute->position);
        $this->assertEquals($options, $attribute->configuration->get('options'));
    }

    /** @test */
    public function can_handle_emtpy_position()
    {
        $attributeGroup = AttributeGroup::factory()->create();

        Attribute::factory()->for($attributeGroup)->create([
            'position' => null,
        ]);

        Attribute::factory()->for($attributeGroup)->create([
            'position' => '',
        ]);

        Attribute::factory()->for($attributeGroup)->create([
            'position' => 0,
        ]);

        $this->assertEquals(range(1,3), Attribute::pluck('position')->all());
    }

    /** @test */
    public function can_handle_non_unique_position()
    {
        Attribute::factory()
            ->for(AttributeGroup::factory())
            ->count(3)
            ->create([
                'position' => 1,
            ]);

        $this->assertEquals(range(1,3), Attribute::pluck('position')->all());
    }
}
