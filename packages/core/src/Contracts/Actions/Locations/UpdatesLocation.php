<?php

namespace Lunar\Core\Contracts\Actions\Locations;

use Lunar\Core\Models\Location;

interface UpdatesLocation
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Location $location, array $attributes): Location;
}
