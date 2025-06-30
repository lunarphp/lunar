<?php

namespace Lunar\Database\Factories;

use Lunar\Models\State;
use Lunar\Models\TaxZone;
use Lunar\Models\TaxZoneState;

class TaxZoneStateFactory extends BaseFactory
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
