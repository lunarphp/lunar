<?php

namespace Lunar\Core\Actions\Countries;

use Lunar\Core\Contracts\Actions\Countries\CreatesCountryState;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\State;

class CreateCountryState implements CreatesCountryState
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Country $country, array $attributes): State
    {
        return $country->states()->create($attributes);
    }
}
