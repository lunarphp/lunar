<?php

namespace Lunar\Core\Actions\Fulfilment;

use Lunar\Core\Contracts\Actions\Fulfilment\TransitionsFulfilment;
use Lunar\Core\Enums\FulfilmentStateCategory;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Contracts\Fulfilment as FulfilmentContract;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\States\Fulfilment\FulfilmentState;

/**
 * Move a fulfilment to a target state through the guarded `FulfilmentState`
 * graph (enforced per-method by `MethodAwareTransition`). This is the single
 * place the handed-over timestamp is maintained, keyed on the target state's
 * *category* rather than a literal state name:
 *
 *  - entering a `Fulfilled`-category state stamps `shipped_at` (it reads as
 *    shipped-at / collected-at / provisioned-at per method), unless it is
 *    already set — so an undo-return back to a `Fulfilled` state keeps the
 *    original timestamp;
 *  - reverting from a `Fulfilled` state back to an `Outstanding` one un-stamps
 *    it and drops any tracking — the parcel was never really handed over.
 *
 * The dedicated `ShipFulfilment` / `FulfilFulfilment` verbs delegate their
 * transition here (ship additionally records tracking). An illegal transition
 * throws and the action is a no-op.
 */
class TransitionFulfilment implements TransitionsFulfilment
{
    /**
     * @param  class-string<FulfilmentState>  $state
     */
    public function execute(FulfilmentContract $fulfilment, string $state): Fulfilment
    {
        /** @var Fulfilment $fulfilment */
        return DB::transaction(function () use ($fulfilment, $state) {
            $fromCategory = $fulfilment->state->category();

            $fulfilment->state->transitionTo($state);

            $toCategory = $fulfilment->state->category();

            if ($toCategory === FulfilmentStateCategory::Fulfilled && blank($fulfilment->shipped_at)) {
                $fulfilment->forceFill(['shipped_at' => now()])->save();
            } elseif ($fromCategory === FulfilmentStateCategory::Fulfilled
                && $toCategory === FulfilmentStateCategory::Outstanding) {
                $fulfilment->trackings()->delete();
                $fulfilment->forceFill(['shipped_at' => null])->save();
            }

            return $fulfilment->refresh();
        });
    }
}
