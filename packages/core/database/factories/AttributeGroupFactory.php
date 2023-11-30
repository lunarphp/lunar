<?php

namespace Lunar\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Lunar\Models\AttributeGroup;
use Lunar\Models\ProductType;

class AttributeGroupFactory extends Factory
{
    private static $position = 1;

    protected $model = AttributeGroup::class;

    public function definition(): array
    {
        return [
            'attributable_type' => ProductType::class,
            'name' => collect([
                'en' => $this->faker->name(),
            ]),
            'handle' => Str::slug($this->faker->name()),
            'position' => self::$position++,
        ];
    }
}
