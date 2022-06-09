<?php

namespace GetCandy\Discounts\Database\Factories;

use GetCandy\Discounts\Models\DiscountReward;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiscountRewardFactory extends Factory
{
    protected $model = DiscountReward::class;

    public function definition(): array
    {
        return [
            'driver' => 'test',
            'data' => [],
        ];
    }
}
