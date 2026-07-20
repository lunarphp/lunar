<?php

namespace Lunar\Core\Actions\Countries;

use Lunar\Core\Contracts\Actions\Countries\UpdatesCountry;
use Lunar\Core\Models\Country;

class UpdateCountry implements UpdatesCountry
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Country $country, array $attributes): Country
    {
        $country->update($attributes);

        return $country;
    }
}
