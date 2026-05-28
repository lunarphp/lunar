<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\States\Order\FulfilmentState;
use Lunar\Core\States\Order\OrderState;
use Lunar\Core\States\Order\PaymentState;

/**
 * Catalogue + transition table + resolver for the three order-state machines.
 *
 * Implementations declare which payment / fulfilment / order states exist,
 * which transitions are legal, and how a (payment, fulfilment) pair resolves
 * to an order_status. The abstract State base classes (`PaymentState`,
 * `FulfilmentState`, `OrderState`) read from the bound implementation to
 * register their states and transitions — so swapping this contract is the
 * single seam for adding bespoke order states.
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
     * @return array<class-string<PaymentState>>
     */
    public function paymentStates(): array;

    /**
     * @return array<class-string<FulfilmentState>>
     */
    public function fulfilmentStates(): array;

    /**
     * @return array<class-string<OrderState>>
     */
    public function orderStates(): array;

    /**
     * @return array<class-string<PaymentState>, list<class-string<PaymentState>>>
     */
    public function paymentTransitions(): array;

    /**
     * @return array<class-string<FulfilmentState>, list<class-string<FulfilmentState>>>
     */
    public function fulfilmentTransitions(): array;

    /**
     * @return class-string<PaymentState>
     */
    public function defaultPaymentState(): string;

    /**
     * @return class-string<FulfilmentState>
     */
    public function defaultFulfilmentState(): string;

    /**
     * @return class-string<OrderState>
     */
    public function defaultOrderState(): string;

    /**
     * @return class-string<OrderState>
     */
    public function resolveOrderState(PaymentState $payment, FulfilmentState $fulfilment): string;
}
