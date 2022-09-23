<?php

namespace Lunar\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Lunar\Models\State;
use Lunar\Models\TaxZone;
use Lunar\Models\TaxZoneState;

class TaxZoneStateFactory extends Factory
{
    protected $model = TaxZoneState::class;

    public function definition(): array
    {
        return [
            'tax_zone_id' => TaxZone::factory(),
            'state_id'    => State::factory(),
        ];
    }
}
