<?php

namespace Lunar\Core\Contracts\Actions\Fulfilment;

use Lunar\Core\Models\Contracts\Fulfilment as FulfilmentContract;
use Lunar\Core\Models\Fulfilment;

interface CancelsFulfilment
{
    /**
     * Cancel a non-terminal fulfilment, returning its quantities to the
     * order's unfulfilled pool.
     */
    public function execute(FulfilmentContract $fulfilment): Fulfilment;
}
