<?php

namespace Lunar\Panel\Support;

use Illuminate\Support\Collection;
use Lunar\Core\Contracts\FulfilmentStateConfig;
use Lunar\Core\Enums\FulfilmentStateCategory;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\States\Fulfilment\FulfilmentState;

/**
 * The status transitions the panel offers for a fulfilment, mirroring the
 * Filament admin's "Update status" menu rules: the guarded graph already
 * filters to the fulfilment's method; on top of that, reverting to the
 * method's default state is excluded (that is the destructive "Cancel
 * fulfilment" action), `Cancelled`-category targets stay programmatic-only,
 * and a held fulfilment cannot advance to a `Fulfilled`-category state.
 *
 * Each entry carries `via` — which endpoint/dialog the client routes the
 * target through — and `notify`, whether a customer notification is
 * configured for the target state (the cue to render the notify toggle).
 */
class FulfilmentTransitions
{
    /**
     * @return Collection<int, array{state: FulfilmentState, name: string, label: string, via: string, notify: bool}>
     */
    public static function for(Fulfilment $fulfilment): Collection
    {
        $method = $fulfilment->method();
        $fulfilledState = $method->fulfilledState();
        $defaultState = $method->defaultState();
        $config = app(FulfilmentStateConfig::class);

        return collect($fulfilment->state->transitionableStateInstances())
            ->reject(fn (FulfilmentState $state) => $state::class === $defaultState
                || $state->category() === FulfilmentStateCategory::Cancelled
                || ($fulfilment->isOnHold() && $state->category() === FulfilmentStateCategory::Fulfilled))
            ->map(fn (FulfilmentState $state) => [
                'state' => $state,
                'name' => $state::$name,
                'label' => $state->label(),
                'via' => match (true) {
                    $state::class === $fulfilledState => $method->usesTracking() ? 'ship' : 'fulfil',
                    $state->category() === FulfilmentStateCategory::Returned => 'return',
                    default => 'transition',
                },
                'notify' => filled($config->notificationsFor($state)),
            ])
            ->values();
    }
}
