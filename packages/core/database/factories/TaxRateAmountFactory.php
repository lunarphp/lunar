<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\TaxClass;
use Lunar\Core\Models\TaxRate;
use Lunar\Core\Models\TaxRateAmount;

class TaxRateAmountFactory extends BaseFactory
{
    protected $model = TaxRateAmount::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::ulid(),
            'tax_rate_id' => TaxRate::factory(),
            'tax_class_id' => TaxClass::factory(),
            'percentage' => 20,
        ];
    }
}
