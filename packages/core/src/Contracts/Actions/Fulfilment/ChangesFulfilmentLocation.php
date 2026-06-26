<?php

namespace Lunar\Core\Contracts\Actions\Fulfilment;

use Lunar\Core\Models\Contracts\Fulfilment as FulfilmentContract;
use Lunar\Core\Models\Fulfilment;

interface ChangesFulfilmentLocation
{
    /**
     * Reassign a pre-ship fulfilment to a different location.
     */
    public function execute(FulfilmentContract $fulfilment, int $locationId): Fulfilment;
}
