<?php

namespace Lunar\Core\Database\Factories;

use Lunar\Core\Models\State;
use Lunar\Core\Models\TaxZone;
use Lunar\Core\Models\TaxZoneState;

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
