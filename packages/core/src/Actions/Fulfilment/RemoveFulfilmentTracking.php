<?php

namespace Lunar\Core\Actions\Fulfilment;

use Lunar\Core\Contracts\Actions\Fulfilment\RemovesFulfilmentTracking;
use Lunar\Core\Models\Contracts\FulfilmentTracking as FulfilmentTrackingContract;
use Lunar\Core\Models\FulfilmentTracking;

/**
 * Remove a single tracking reference from a fulfilment. Tracking does not feed
 * the fulfilment rollup, so deleting one leaves the fulfilment's state untouched.
 */
class RemoveFulfilmentTracking implements RemovesFulfilmentTracking
{
    public function execute(FulfilmentTrackingContract $tracking): void
    {
        /** @var FulfilmentTracking $tracking */
        $tracking->delete();
    }
}
