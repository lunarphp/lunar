<?php

namespace Lunar\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Models\CollectionGroup;

class CollectionGroupFactory extends BaseFactory
{
    protected $model = CollectionGroup::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->word;

        return [
            'name' => $name,
            'handle' => Str::slug($name),
        ];
    }
}
