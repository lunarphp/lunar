<?php

namespace Lunar\Core\Contracts\Actions\Countries;

use Lunar\Core\Models\Country;

interface DeletesCountry
{
    public function execute(Country $country): void;
}
