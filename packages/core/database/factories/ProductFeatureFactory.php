<?php

namespace GetCandy\Database\Factories;

use GetCandy\Models\ProductFeature;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFeatureFactory extends Factory
{
    private static $position = 1;

    protected $model = ProductFeature::class;

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
