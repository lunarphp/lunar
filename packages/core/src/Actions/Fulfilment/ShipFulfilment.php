<?php

namespace Lunar\Core\Actions\Fulfilment;

use Lunar\Core\Contracts\Actions\Fulfilment\ShipsFulfilment;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Contracts\Fulfilment as FulfilmentContract;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\States\Fulfilment\Shipped;

/**
 * Transition a `Pending` / `InProgress` fulfilment to `Shipped`, stamping
 * `shipped_at` and recording tracking details. The state change is routed
 * through the guarded `FulfilmentState` graph — an illegal transition throws
 * and the action is a no-op.
 */
final class ShipFulfilment implements ShipsFulfilment
{
    public function execute(FulfilmentContract $fulfilment, array $tracking = []): Fulfilment
    {
        /** @var Fulfilment $fulfilment */
        return DB::transaction(function () use ($fulfilment, $tracking) {
            $fulfilment->state->transitionTo(Shipped::class);

            $fulfilment->forceFill(array_filter([
                'shipped_at' => now(),
                'tracking_number' => $tracking['tracking_number'] ?? $fulfilment->tracking_number,
                'tracking_url' => $tracking['tracking_url'] ?? $fulfilment->tracking_url,
                'shipping_method' => $tracking['shipping_method'] ?? $fulfilment->shipping_method,
            ], fn ($value) => $value !== null))->save();

            return $fulfilment->refresh();
        });
    }

    /**
     * Whether the fulfilment can be shipped, per the `FulfilmentState` graph.
     */
    public static function canRun(FulfilmentContract $fulfilment): bool
    {
        /** @var Fulfilment $fulfilment */
        return $fulfilment->state->canTransitionTo(Shipped::class);
    }
}
