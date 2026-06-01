<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\States\Order\OrderState;

/**
 * Catalogue + transition table for the order lifecycle machine.
 *
 * Implementations declare which order states exist, which transitions are
 * legal, and the default state. The abstract `OrderState` base reads from the
 * bound implementation to register its states and transitions — so swapping
 * this contract is the single seam for reshaping the order lifecycle.
 *
 * Spatie's State base caches the resolved state mapping per class for the
 * lifetime of the process. Bind your implementation during service-provider
 * `register()` so the catalogue is in place before any model uses the cast.
 * Under Laravel Octane the cache survives between requests — bindings set at
 * runtime won't be visible to already-cached state machines.
 */
interface OrderStateConfig
{
    /**
     * @return array<class-string<OrderState>>
     */
    public function orderStates(): array;

    /**
     * @return array<class-string<OrderState>, list<class-string<OrderState>>>
     */
    public function orderTransitions(): array;

    /**
     * @return class-string<OrderState>
     */
    public function defaultOrderState(): string;

    /**
     * Notification classes to dispatch when an order transitions into the
     * given status. Each class is instantiated with the order and delivered
     * via `$order->notify()`.
     *
     * @return array<class-string>
     */
    public function notificationsFor(OrderState $state): array;
}
