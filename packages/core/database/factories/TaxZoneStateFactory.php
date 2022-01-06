<?php

namespace GetCandy\Database\Factories;

use GetCandy\Models\State;
use GetCandy\Models\TaxZone;
use GetCandy\Models\TaxZoneState;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaxZoneStateFactory extends Factory
{
    protected $model = TaxZoneState::class;

    public function definition(): array
    {
        return [
            'tax_zone_id' => TaxZone::factory(),
            'state_id' => State::factory(),
        ];
    }
}
