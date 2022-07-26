<?php

namespace GetCandy\Database\Factories;

use GetCandy\Models\ProductOption;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductOptionFactory extends Factory
{
    private static $position = 1;

    protected $model = ProductOption::class;

    public function definition(): array
    {
        $name = $this->faker->name;

        return [
            'handle' => Str::slug($name),
            'name' => [
                'en' => $name,
            ],
            'position' => self::$position++,
        ];
    }
}
