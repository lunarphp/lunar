<?php

namespace Lunar\Database\Factories;

use Lunar\Models\AttributeGroup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AttributeGroupFactory extends Factory
{
    private static $position = 1;

    protected $model = AttributeGroup::class;

    public function definition(): array
    {
        return [
            'attributable_type' => 'product_type',
            'name'              => collect([
                'en' => $this->faker->name(),
            ]),
            'handle'   => Str::slug($this->faker->name()),
            'position' => self::$position++,
        ];
    }
}
