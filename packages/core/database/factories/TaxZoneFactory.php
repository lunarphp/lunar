<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\TaxZone;

class TaxZoneFactory extends BaseFactory
{
    protected $model = TaxZone::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::ulid(),
            'name' => $this->faker->name,
            'zone_type' => $this->faker->randomElement(['country', 'postcode', 'state']),
            'active' => true,
            'default' => true,
        ];
    }
}
