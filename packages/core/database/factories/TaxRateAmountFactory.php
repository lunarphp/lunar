<?php

namespace Lunar\Database\Factories;

use Lunar\Models\TaxClass;
use Lunar\Models\TaxRate;
use Lunar\Models\TaxRateAmount;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaxRateAmountFactory extends Factory
{
    protected $model = TaxRateAmount::class;

    public function definition(): array
    {
        return [
            'tax_rate_id'  => TaxRate::factory(),
            'tax_class_id' => TaxClass::factory(),
            'percentage'   => 20,
        ];
    }
}
