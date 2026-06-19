<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\States\Fulfilment\FulfilmentState;

/**
 * Catalogue + transition table for the per-fulfilment lifecycle.
 *
 * Implementations declare which fulfilment states exist, which transitions are
 * legal, and the default state. The abstract `FulfilmentState` base reads from
 * the bound implementation — so swapping this contract is the single seam for
 * reshaping the fulfilment lifecycle.
 *
 * Bind your implementation during service-provider `register()` so the
 * catalogue is in place before any model uses the cast (Spatie caches the
 * resolved mapping per class for the lifetime of the process).
 */
interface FulfilmentStateConfig
{
    /**
     * @return array<class-string<FulfilmentState>>
     */
    public function fulfilmentStates(): array;

    /**
     * @return array<class-string<FulfilmentState>, list<class-string<FulfilmentState>>>
     */
    public function fulfilmentTransitions(): array;

    /**
     * @return class-string<FulfilmentState>
     */
    public function defaultFulfilmentState(): string;

    /**
     * Notification classes to dispatch when a fulfilment transitions into the
     * given state. Each class is instantiated with the fulfilment.
     *
     * @return array<class-string>
     */
    public function notificationsFor(FulfilmentState $state): array;
}
