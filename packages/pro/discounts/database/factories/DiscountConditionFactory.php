<?php

namespace GetCandy\Discounts\Database\Factories;

use GetCandy\Discounts\Models\DiscountCondition;
use GetCandy\Discounts\Models\DiscountReward;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiscountConditionFactory extends Factory
{
    protected $model = DiscountCondition::class;

    public function definition(): array
    {
        return [
            'driver' => 'test',
            'data' => [],
        ];
    }
}
