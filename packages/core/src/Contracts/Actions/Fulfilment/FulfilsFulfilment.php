<?php

namespace Lunar\Core\Contracts\Actions\Fulfilment;

use Lunar\Core\Models\Fulfilment;

interface FulfilsFulfilment
{
    /**
     * Advance a fulfilment to its method's canonical "done" state with no
     * tracking (collection → collected, digital → provisioned, …), routed
     * through the guarded `FulfilmentState` graph — an illegal move throws.
     * Pass `$notify: false` to suppress the customer notification this state
     * change would otherwise trigger.
     */
    public function execute(Fulfilment $fulfilment, bool $notify = true): Fulfilment;
}
