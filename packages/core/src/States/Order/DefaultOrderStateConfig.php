<?php

namespace Lunar\Core\States\Order;

use Lunar\Core\Contracts\OrderStateConfig;
use Lunar\Core\States\Order\Fulfilment\Fulfilled;
use Lunar\Core\States\Order\Fulfilment\FulfilmentStatus;
use Lunar\Core\States\Order\Fulfilment\PartiallyFulfilled;
use Lunar\Core\States\Order\Fulfilment\PartiallyReturned;
use Lunar\Core\States\Order\Fulfilment\Returned as FulfilmentReturned;
use Lunar\Core\States\Order\Order\AwaitingPayment;
use Lunar\Core\States\Order\Order\Cancelled;
use Lunar\Core\States\Order\Order\Complete;
use Lunar\Core\States\Order\Order\InProcess;
use Lunar\Core\States\Order\Order\OnHold;
use Lunar\Core\States\Order\Order\PartiallyShipped;
use Lunar\Core\States\Order\Order\PaymentFailed;
use Lunar\Core\States\Order\Order\Refunded as OrderRefunded;
use Lunar\Core\States\Order\Order\Returned as OrderReturned;
use Lunar\Core\States\Order\Order\Shipped;
use Lunar\Core\States\Order\Payment\Authorized;
use Lunar\Core\States\Order\Payment\Paid;
use Lunar\Core\States\Order\Payment\PartiallyRefunded;
use Lunar\Core\States\Order\Payment\PaymentState;
use Lunar\Core\States\Order\Payment\Pending as PaymentPending;
use Lunar\Core\States\Order\Payment\Refunded as PaymentRefunded;
use Lunar\Core\States\Order\Payment\Voided;

class DefaultOrderStateConfig implements OrderStateConfig
{
    public function orderStates(): array
    {
        return [
            AwaitingPayment::class,
            PaymentFailed::class,
            InProcess::class,
            PartiallyShipped::class,
            Shipped::class,
            Complete::class,
            OrderReturned::class,
            OrderRefunded::class,
            OnHold::class,
            Cancelled::class,
        ];
    }

    public function orderTransitions(): array
    {
        return [
            AwaitingPayment::class => [InProcess::class, PaymentFailed::class, PartiallyShipped::class, Shipped::class, OnHold::class, Cancelled::class, OrderRefunded::class],
            PaymentFailed::class => [AwaitingPayment::class, InProcess::class, Cancelled::class, OrderRefunded::class],
            InProcess::class => [PartiallyShipped::class, Shipped::class, OrderReturned::class, OnHold::class, Cancelled::class, OrderRefunded::class],
            PartiallyShipped::class => [Shipped::class, OrderReturned::class, OnHold::class, Cancelled::class, OrderRefunded::class],
            Shipped::class => [Complete::class, OrderReturned::class, OrderRefunded::class],
            Complete::class => [OrderReturned::class, OrderRefunded::class],
            OrderReturned::class => [OrderRefunded::class],
            OnHold::class => [AwaitingPayment::class, InProcess::class, PartiallyShipped::class, Shipped::class, Cancelled::class],
            Cancelled::class => [OrderRefunded::class],
            OrderRefunded::class => [],
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

    public function overrideStates(): array
    {
        return [
            OnHold::class,
            Cancelled::class,
            OrderRefunded::class,
        ];
    }

    public function computeOrderStatus(PaymentState $payment, FulfilmentStatus $fulfilment): string
    {
        // A return (full or partial) is the headline regardless of payment.
        if ($fulfilment instanceof FulfilmentReturned || $fulfilment instanceof PartiallyReturned) {
            return OrderReturned::class;
        }

        // A voided / failed authorization with nothing captured reads as a
        // failed payment at the headline.
        if ($payment instanceof Voided) {
            return PaymentFailed::class;
        }

        // No money in yet (nothing captured): still awaiting payment.
        if ($payment instanceof PaymentPending || $payment instanceof Authorized) {
            return AwaitingPayment::class;
        }

        // Fully refunded — surfaces as the Refunded override state.
        if ($payment instanceof PaymentRefunded) {
            return OrderRefunded::class;
        }

        // From here payment is Paid, PartiallyPaid or PartiallyRefunded.
        // A fully-captured order is driven by its fulfilment progress.
        if ($payment instanceof Paid || $payment instanceof PartiallyRefunded) {
            if ($fulfilment instanceof Fulfilled) {
                return Shipped::class;
            }

            if ($fulfilment instanceof PartiallyFulfilled) {
                return PartiallyShipped::class;
            }
        }

        // PartiallyPaid (any fulfilment) and Paid + Unfulfilled.
        return InProcess::class;
    }
}
