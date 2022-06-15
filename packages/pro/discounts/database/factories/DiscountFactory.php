<?php

namespace GetCandy\Discounts\Database\Factories;

use GetCandy\Discounts\Models\Discount;
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
            'handle' => Str::slug($name),
            'starts_at' => now(),
            'attribute_data'  => collect([
                'name'        => new Text($name),
                'description' => new Text($this->faker->sentence),
            ]),
        ];
    }
}
