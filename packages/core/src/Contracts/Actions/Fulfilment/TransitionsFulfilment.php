<?php

namespace Lunar\Core\Contracts\Actions\Fulfilment;

use Lunar\Core\Models\Contracts\Fulfilment as FulfilmentContract;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\States\Fulfilment\FulfilmentState;

interface TransitionsFulfilment
{
    /**
     * Move a fulfilment to the given state, routed through the guarded
     * `FulfilmentState` graph — an illegal transition throws. Pass
     * `$notify: false` to suppress the customer notification this state change
     * would otherwise trigger.
     *
     * @param  class-string<FulfilmentState>  $state
     */
    public function execute(FulfilmentContract $fulfilment, string $state, bool $notify = true): Fulfilment;
}
