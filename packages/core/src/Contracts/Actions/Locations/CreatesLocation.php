<?php

namespace Lunar\Core\Contracts\Actions\Locations;

use Lunar\Core\Models\Location;

interface CreatesLocation
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): Location;
}
