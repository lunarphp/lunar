<?php

namespace Lunar\Core\Contracts\Actions\Fulfilment;

use Lunar\Core\Models\Contracts\Fulfilment as FulfilmentContract;
use Lunar\Core\Models\Fulfilment;

interface HoldsFulfilment
{
    /**
     * Place a fulfilment on hold, blocking it from shipping until released.
     */
    public function execute(FulfilmentContract $fulfilment, ?string $reason = null, ?string $note = null): Fulfilment;
}
