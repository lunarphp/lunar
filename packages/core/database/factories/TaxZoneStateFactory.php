<?php

namespace Lunar\Database\Factories;

use Lunar\Models\State;
use Lunar\Models\TaxZone;
use Lunar\Models\TaxZoneState;
use Illuminate\Database\Eloquent\Factories\Factory;

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
