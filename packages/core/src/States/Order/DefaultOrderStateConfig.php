<?php

namespace Lunar\Core\States\Order;

use Lunar\Core\Contracts\OrderStateConfig;
use Lunar\Core\States\Order\Order\AwaitingPayment;
use Lunar\Core\States\Order\Order\Backordered;
use Lunar\Core\States\Order\Order\Cancelled;
use Lunar\Core\States\Order\Order\Complete;
use Lunar\Core\States\Order\Order\InProcess;
use Lunar\Core\States\Order\Order\OnHold;
use Lunar\Core\States\Order\Order\PartiallyShipped;
use Lunar\Core\States\Order\Order\PaymentFailed;
use Lunar\Core\States\Order\Order\Refunded;
use Lunar\Core\States\Order\Order\Returned;
use Lunar\Core\States\Order\Order\Shipped;

class DefaultOrderStateConfig implements OrderStateConfig
{
    public function orderStates(): array
    {
        return [
            AwaitingPayment::class,
            PaymentFailed::class,
            Backordered::class,
            InProcess::class,
            PartiallyShipped::class,
            Shipped::class,
            Complete::class,
            Returned::class,
            Refunded::class,
            OnHold::class,
            Cancelled::class,
        ];
    }

    public function orderTransitions(): array
    {
        return [
            AwaitingPayment::class => [InProcess::class, PaymentFailed::class, Backordered::class, OnHold::class, Cancelled::class],
            PaymentFailed::class => [AwaitingPayment::class, Cancelled::class],
            Backordered::class => [InProcess::class, OnHold::class, Cancelled::class],
            InProcess::class => [PartiallyShipped::class, Shipped::class, OnHold::class, Cancelled::class],
            PartiallyShipped::class => [Shipped::class, Returned::class, Cancelled::class],
            Shipped::class => [Complete::class, Returned::class],
            Complete::class => [Returned::class, Refunded::class],
            Returned::class => [Refunded::class],
            OnHold::class => [AwaitingPayment::class, InProcess::class, Cancelled::class],
            Cancelled::class => [Refunded::class],
            Refunded::class => [],
        ];
    }

    public function defaultOrderState(): string
    {
        return AwaitingPayment::class;
    }

    public function notificationsFor(OrderState $state): array
    {
        /** @var array<class-string> $notifications */
        $notifications = (array) config('lunar.orders.notifications.'.$state::$name, []);

        return $notifications;
    }
}
