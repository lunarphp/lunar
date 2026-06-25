<?php

namespace Lunar\Core\Contracts\Actions\Fulfilment;

use Lunar\Core\Models\Contracts\Fulfilment as FulfilmentContract;
use Lunar\Core\Models\Fulfilment;

interface ReleasesFulfilment
{
    /**
     * Release a held fulfilment, clearing the hold so it can ship again.
     */
    public function execute(FulfilmentContract $fulfilment): Fulfilment;
}
