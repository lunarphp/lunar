<?php

namespace Lunar\Core\Actions\Fulfilment;

use Lunar\Core\Contracts\Actions\Fulfilment\TransitionsFulfilment;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Contracts\Fulfilment as FulfilmentContract;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\States\Fulfilment\FulfilmentState;

/**
 * Move a fulfilment to an arbitrary target state through the guarded
 * `FulfilmentState` graph. The dedicated `ShipFulfilment` /  `CancelFulfilment`
 * / `ReturnFulfilment` actions carry extra behaviour (stamping `shipped_at`,
 * recording tracking); this is the plain transition used for the remaining
 * moves (e.g. `Pending` → `InProgress`). An illegal transition throws.
 */
final class TransitionFulfilment implements TransitionsFulfilment
{
    /**
     * @param  class-string<FulfilmentState>  $state
     */
    public function execute(FulfilmentContract $fulfilment, string $state): Fulfilment
    {
        /** @var Fulfilment $fulfilment */
        return DB::transaction(function () use ($fulfilment, $state) {
            $fulfilment->state->transitionTo($state);

            return $fulfilment->refresh();
        });
    }
}
