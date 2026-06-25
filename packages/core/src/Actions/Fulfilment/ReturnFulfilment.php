<?php

namespace Lunar\Core\Actions\Fulfilment;

use Lunar\Core\Contracts\Actions\Fulfilment\ReturnsFulfilment;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Contracts\Fulfilment as FulfilmentContract;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\States\Fulfilment\Returned;

/**
 * Mark a shipped fulfilment as returned, feeding `PartiallyReturned` /
 * `Returned` at the order level. Independent of refunds: returning never
 * issues a refund and a refund never marks a return.
 */
class ReturnFulfilment implements ReturnsFulfilment
{
    public function execute(FulfilmentContract $fulfilment, bool $notify = true): Fulfilment
    {
        /** @var Fulfilment $fulfilment */
        return DB::transaction(function () use ($fulfilment, $notify) {
            // Carry the "notify the customer" intent into the observer, which
            // reads it off the instance when it dispatches FulfilmentStatusUpdated.
            $fulfilment->notifyOnStatusChange = $notify;

            $fulfilment->state->transitionTo(Returned::class);

            return $fulfilment->refresh();
        });
    }

    /**
     * Whether the fulfilment can be returned, per the `FulfilmentState` graph.
     */
    public static function canRun(FulfilmentContract $fulfilment): bool
    {
        /** @var Fulfilment $fulfilment */
        return $fulfilment->state->canTransitionTo(Returned::class);
    }
}
