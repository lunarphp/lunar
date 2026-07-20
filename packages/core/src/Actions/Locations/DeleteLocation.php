<?php

namespace Lunar\Core\Actions\Locations;

use Lunar\Core\Contracts\Actions\Locations\DeletesLocation;
use Lunar\Core\Exceptions\LocationActionException;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\StockLevel;
use Lunar\Core\Models\StockMovement;

/**
 * Delete a location. Locations with fulfilments or stock history are kept —
 * the database restricts those deletes anyway — so inventory context is
 * never lost. The default location is also kept: make another location the
 * default first.
 */
class DeleteLocation implements DeletesLocation
{
    public function execute(Location $location): void
    {
        if ($location->default) {
            throw new LocationActionException('Cannot delete the default location. Make another location the default first.');
        }

        if ($location->fulfilments()->exists()) {
            throw new LocationActionException('Cannot delete a location with fulfilments.');
        }

        $hasStock = StockLevel::query()->where('location_id', $location->id)->exists()
            || StockMovement::query()->where('location_id', $location->id)->exists();

        if ($hasStock) {
            throw new LocationActionException('Cannot delete a location with stock history.');
        }

        $location->delete();
    }
}
