<?php

namespace GetCandy\Database\Factories;

use GetCandy\Models\Discount;
use GetCandy\FieldTypes\Text;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DiscountFactory extends Factory
{
    protected $model = Discount::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->name;

        return [
            'name' => $name,
            'handle' => Str::slug($name),
            'starts_at' => now(),
        ];
    }
}
