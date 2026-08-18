<?php

namespace Lunar\Core\Contracts\Actions\Countries;

use Lunar\Core\Models\Country;

interface UpdatesCountry
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Country $country, array $attributes): Country;
}
