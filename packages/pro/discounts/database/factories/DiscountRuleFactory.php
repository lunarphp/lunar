<?php

namespace GetCandy\Discounts\Database\Factories;

use GetCandy\Discounts\Models\DiscountRule;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiscountRuleFactory extends Factory
{
    protected $model = DiscountRule::class;

    public function definition(): array
    {
        return [
            'driver' => 'test',
        ];
    }
}
