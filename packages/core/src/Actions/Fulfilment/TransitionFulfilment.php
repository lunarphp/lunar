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
class TransitionFulfilment implements TransitionsFulfilment
{
    /**
     * States that precede dispatch — reverting to one un-stamps `shipped_at`.
     */
    private const PRE_SHIP_STATES = ['pending', 'in-progress'];

    /**
     * @param  class-string<FulfilmentState>  $state
     */
    public function execute(FulfilmentContract $fulfilment, string $state): Fulfilment
    {
        /** @var Fulfilment $fulfilment */
        return DB::transaction(function () use ($fulfilment, $state) {
            $fulfilment->state->transitionTo($state);

            // Reverting a shipped parcel to a pre-ship state un-ships it, so its
            // shipment details (timestamp + tracking) no longer apply.
            if (in_array($state::$name, self::PRE_SHIP_STATES, true) && $fulfilment->shipped_at) {
                $fulfilment->trackings()->delete();
                $fulfilment->forceFill(['shipped_at' => null])->save();
            }

            return $fulfilment->refresh();
        });
    }
}
