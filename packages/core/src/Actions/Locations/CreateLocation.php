<?php

namespace Lunar\Core\Actions\Locations;

use Lunar\Core\Contracts\Actions\Locations\CreatesLocation;
use Lunar\Core\Models\Location;

/**
 * Create a location, ensuring at most one location is ever marked default.
 */
class CreateLocation implements CreatesLocation
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): Location
    {
        if ($attributes['default'] ?? false) {
            Location::query()->where('default', true)->update(['default' => false]);
        }

        return Location::create($attributes);
    }
}
