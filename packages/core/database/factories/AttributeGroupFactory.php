<?php

namespace Lunar\Core\Database\Factories;

use Lunar\Core\Models\AttributeGroup;

class AttributeGroupFactory extends BaseFactory
{
    private static $position = 1;

    protected $model = AttributeGroup::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'handle' => $this->faker->unique()->slug(),
            'position' => self::$position++,
            'system' => false,
        ];
    }
}
