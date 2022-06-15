<?php

namespace GetCandy\Discounts\Database\Factories;

use GetCandy\Discounts\Models\DiscountRuleset;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiscountRulesetFactory extends Factory
{
    protected $model = DiscountRuleset::class;

    public function definition(): array
    {
        return [
            'criteria' => 'all',
        ];
    }
}
