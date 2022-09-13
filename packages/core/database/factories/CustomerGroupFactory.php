<?php

namespace Lunar\Database\Factories;

use Lunar\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CustomerGroupFactory extends Factory
{
    protected $model = CustomerGroup::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->word;

        return [
            'name'   => $name,
            'handle' => Str::slug($name),
        ];
    }
}
