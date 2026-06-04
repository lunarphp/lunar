<?php

namespace Lunar\Core\Contracts\Actions\Fulfilment;

use Lunar\Core\Models\Contracts\Fulfilment as FulfilmentContract;
use Lunar\Core\Models\Fulfilment;

interface ReturnsFulfilment
{
    /**
     * Mark a shipped fulfilment as returned. Independent of refunds — a
     * return never issues a refund.
     */
    public function execute(FulfilmentContract $fulfilment): Fulfilment;
}
