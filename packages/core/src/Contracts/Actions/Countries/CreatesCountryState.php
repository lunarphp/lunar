<?php

namespace Lunar\Core\Contracts\Actions\Countries;

use Lunar\Core\Models\Country;
use Lunar\Core\Models\State;

interface CreatesCountryState
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Country $country, array $attributes): State;
}
