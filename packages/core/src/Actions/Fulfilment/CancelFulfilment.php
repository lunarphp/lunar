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
 */
final class CancelFulfilment implements CancelsFulfilment
{
    public function execute(FulfilmentContract $fulfilment): Fulfilment
    {
        /** @var Fulfilment $fulfilment */
        return DB::transaction(function () use ($fulfilment) {
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
