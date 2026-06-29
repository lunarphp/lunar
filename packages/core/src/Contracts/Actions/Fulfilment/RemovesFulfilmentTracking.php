<?php

namespace Lunar\Core\Contracts\Actions\Fulfilment;

use Lunar\Core\Models\FulfilmentTracking;

interface RemovesFulfilmentTracking
{
    /**
     * Remove a tracking reference from its fulfilment.
     */
    public function execute(FulfilmentTracking $tracking): void;
}
