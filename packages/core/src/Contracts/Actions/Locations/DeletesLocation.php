<?php

namespace Lunar\Core\Contracts\Actions\Locations;

use Lunar\Core\Models\Location;

interface DeletesLocation
{
    public function execute(Location $location): void;
}
