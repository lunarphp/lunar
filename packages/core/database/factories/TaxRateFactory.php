<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\TaxRate;
use Lunar\Core\Models\TaxZone;

class TaxRateFactory extends BaseFactory
{
    protected $model = TaxRate::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::ulid(),
            'tax_zone_id' => TaxZone::factory(),
            'name' => $this->faker->name,
            'priority' => $this->faker->numberBetween(1, 50),
        ];
    }
}
