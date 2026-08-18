<?php

namespace Lunar\Core\Actions\Locations;

use Lunar\Core\Contracts\Actions\Locations\UpdatesLocation;
use Lunar\Core\Exceptions\LocationActionException;
use Lunar\Core\Models\Location;

/**
 * Update a location, ensuring at most one location is ever marked default.
 * The default flag moves by promoting another location, never by unsetting.
 */
class UpdateLocation implements UpdatesLocation
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Location $location, array $attributes): Location
    {
        if ($location->default && array_key_exists('default', $attributes) && ! $attributes['default']) {
            throw new LocationActionException('Cannot unset the default location. Make another location the default instead.');
        }

        if ($attributes['default'] ?? false) {
            Location::query()->where('default', true)->where('id', '!=', $location->id)->update(['default' => false]);
        }

        $location->update($attributes);

        return $location;
    }
}
