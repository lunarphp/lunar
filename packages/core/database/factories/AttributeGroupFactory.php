<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\AttributeGroup;

class AttributeGroupFactory extends BaseFactory
{
    private static $position = 1;

    protected $model = AttributeGroup::class;

    public function definition(): array
    {
        return [
            'attributable_type' => 'product_type',
            'name' => collect([
                'en' => $this->faker->name(),
            ]),
            'handle' => Str::slug($this->faker->name()),
            'position' => self::$position++,
        ];
    }
}
