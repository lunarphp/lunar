<?php

namespace Lunar\Core\Actions\Fulfilment;

use Lunar\Core\Contracts\Actions\Fulfilment\HoldsFulfilment;
use Lunar\Core\Events\Fulfilment\FulfilmentHeld;
use Lunar\Core\Models\Fulfilment;

/**
 * Place a pre-ship fulfilment on hold with an optional reason and note. A held
 * fulfilment is blocked from shipping until released. The hold is orthogonal to the
 * `FulfilmentState` graph — the fulfilment keeps its current state.
 */
class HoldFulfilment implements HoldsFulfilment
{
    /**
     * Fulfilment states that may be held — only a fulfilment that hasn't shipped.
     */
    public const HOLDABLE_STATES = ['pending', 'in-progress'];

    public function execute(Fulfilment $fulfilment, ?string $reason = null, ?string $note = null): Fulfilment
    {
        /** @var Fulfilment $fulfilment */
        $fulfilment->forceFill([
            'held_at' => now(),
            'hold_reason' => $reason,
            'hold_note' => $note,
        ])->save();

        FulfilmentHeld::dispatch($fulfilment);

        return $fulfilment;
    }

    /**
     * Whether the fulfilment can be held — pre-ship and not already on hold.
     */
    public static function canRun(Fulfilment $fulfilment): bool
    {
        /** @var Fulfilment $fulfilment */
        return ! $fulfilment->isOnHold()
            && in_array($fulfilment->state::$name, self::HOLDABLE_STATES, true);
    }
}
