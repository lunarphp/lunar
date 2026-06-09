<?php

namespace Lunar\Core\Contracts\Actions\Fulfilment;

use Lunar\Core\Models\Contracts\FulfilmentTracking as FulfilmentTrackingContract;

interface RemovesFulfilmentTracking
{
    /**
     * Remove a tracking reference from its fulfilment.
     */
    public function execute(FulfilmentTrackingContract $tracking): void;
}
