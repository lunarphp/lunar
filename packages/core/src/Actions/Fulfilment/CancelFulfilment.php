<?php

namespace Lunar\Core\Actions\Fulfilment;

use Lunar\Core\Contracts\Actions\Fulfilment\CancelsFulfilment;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Contracts\Fulfilment as FulfilmentContract;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\States\Fulfilment\Cancelled;

/**
 * Cancel a non-terminal fulfilment. The rollup stops counting a cancelled
 * fulfilment, so its quantities return to the order's unfulfilled pool. The
 * state change is routed through the guarded `FulfilmentState` graph.
 *
 * Voiding a fulfilment is plumbing, not a customer-facing milestone — and the
 * `Cancelled` state shares its `$name` with the order-level cancellation
 * notification key — so this never fires the per-fulfilment notification path. The
 * customer-facing cancellation email is the order-level one ({@see CancelOrder}).
 */
class CancelFulfilment implements CancelsFulfilment
{
    public function execute(FulfilmentContract $fulfilment): Fulfilment
    {
        /** @var Fulfilment $fulfilment */
        return DB::transaction(function () use ($fulfilment) {
            // Suppress the per-fulfilment notification — fulfilment voids are never a
            // customer milestone (see the class docblock).
            $fulfilment->notifyOnStatusChange = false;

            $fulfilment->state->transitionTo(Cancelled::class);

            return $fulfilment->refresh();
        });
    }

    /**
     * Whether the fulfilment can be cancelled, per the `FulfilmentState` graph.
     */
    public static function canRun(FulfilmentContract $fulfilment): bool
    {
        /** @var Fulfilment $fulfilment */
        return $fulfilment->state->canTransitionTo(Cancelled::class);
    }
}
