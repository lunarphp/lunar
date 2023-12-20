<?php

namespace Lunar\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Lunar\Models\ProductOption;

class ProductOptionFactory extends Factory
{
    protected $model = ProductOption::class;

    public function definition(): array
    {
        $name = $this->faker->name;

        return [
            'handle' => Str::slug($name),
            'name' => [
                'en' => $name,
            ],
            'label' => [
                'en' => $name,
            ],
        ];
    }
}
