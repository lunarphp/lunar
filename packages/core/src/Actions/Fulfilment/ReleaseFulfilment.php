<?php

namespace Lunar\Core\Actions\Fulfilment;

use Lunar\Core\Contracts\Actions\Fulfilment\ReleasesFulfilment;
use Lunar\Core\Events\Fulfilment\FulfilmentReleased;
use Lunar\Core\Models\Fulfilment;

/**
 * Release a held fulfilment, clearing the hold (timestamp, reason and note) so
 * it can be shipped again.
 */
class ReleaseFulfilment implements ReleasesFulfilment
{
    public function execute(Fulfilment $fulfilment): Fulfilment
    {
        /** @var Fulfilment $fulfilment */
        if (! $fulfilment->isOnHold()) {
            return $fulfilment;
        }

        $fulfilment->forceFill([
            'held_at' => null,
            'hold_reason' => null,
            'hold_note' => null,
        ])->save();

        FulfilmentReleased::dispatch($fulfilment);

        return $fulfilment;
    }

    /**
     * Whether the fulfilment can be released — it is currently on hold.
     */
    public static function canRun(Fulfilment $fulfilment): bool
    {
        /** @var Fulfilment $fulfilment */
        return $fulfilment->isOnHold();
    }
}
