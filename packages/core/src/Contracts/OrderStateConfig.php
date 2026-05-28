<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\States\Order\FulfilmentState;
use Lunar\Core\States\Order\OrderState;
use Lunar\Core\States\Order\PaymentState;

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
     * @return class-string<OrderState>
     */
    public function resolveOrderState(PaymentState $payment, FulfilmentState $fulfilment): string;
}
