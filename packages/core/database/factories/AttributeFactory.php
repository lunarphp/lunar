<?php

namespace GetCandy\Database\Factories;

use GetCandy\Models\Attribute;
use GetCandy\Models\AttributeGroup;
use GetCandy\Models\ProductType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AttributeFactory extends Factory
{
    private static $position = 1;

    protected $model = Attribute::class;

    public function definition(): array
    {
        return [
            'attribute_group_id' => AttributeGroup::factory(),
            'attribute_type'     => ProductType::class,
            'position'           => self::$position++,
            'name'               => [
                'en' => $this->faker->name(),
            ],
            'handle'        => Str::slug($this->faker->name()),
            'section'       => $this->faker->name(),
            'type'          => \GetCandy\FieldTypes\Text::class,
            'required'      => false,
            'default_value' => '',
            'configuration' => [
                'options' => [
                    $this->faker->name(),
                    $this->faker->name(),
                    $this->faker->name(),
                ],
            ],
            'system' => false,
        ];
    }
}
