<?php

namespace Lunar\Core\Actions\Regions;

use Lunar\Core\Contracts\Actions\Regions\DeletesRegion;
use Lunar\Core\Exceptions\RegionActionException;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Region;

/**
 * Delete a region. Regions with order history are kept — historical orders
 * keep their context. The default region is also kept: make another region
 * the default first.
 */
class DeleteRegion implements DeletesRegion
{
    public function execute(Region $region): void
    {
        if ($region->default) {
            throw new RegionActionException('Cannot delete the default region. Make another region the default first.');
        }

        if (Order::query()->where('region_id', $region->id)->exists()) {
            throw new RegionActionException('Cannot delete a region with order history.');
        }

        $region->countries()->detach();
        $region->delete();
    }
}
