<?php

namespace Lunar\Core\Contracts\Actions\Fulfilment;

use Lunar\Core\Models\Fulfilment;

interface ChangesFulfilmentLocation
{
    /**
     * Reassign a pre-ship fulfilment to a different location.
     */
    public function execute(Fulfilment $fulfilment, int $locationId): Fulfilment;
}
