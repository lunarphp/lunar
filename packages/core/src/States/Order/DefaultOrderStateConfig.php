<?php

namespace Lunar\Core\States\Order;

use Lunar\Core\Contracts\OrderStateConfig;
use Lunar\Core\Enums\StateCategory;
use Lunar\Core\States\Order\Fulfilment\Backordered as FulfilmentBackordered;
use Lunar\Core\States\Order\Fulfilment\Delivered as FulfilmentDelivered;
use Lunar\Core\States\Order\Fulfilment\PartiallyShipped as FulfilmentPartiallyShipped;
use Lunar\Core\States\Order\Fulfilment\Processing as FulfilmentProcessing;
use Lunar\Core\States\Order\Fulfilment\Returned as FulfilmentReturned;
use Lunar\Core\States\Order\Fulfilment\Shipped as FulfilmentShipped;
use Lunar\Core\States\Order\Fulfilment\Unfulfilled;
use Lunar\Core\States\Order\Order\AwaitingPayment;
use Lunar\Core\States\Order\Order\Backordered as OrderBackordered;
use Lunar\Core\States\Order\Order\Cancelled;
use Lunar\Core\States\Order\Order\Complete as OrderComplete;
use Lunar\Core\States\Order\Order\InProcess;
use Lunar\Core\States\Order\Order\OnHold;
use Lunar\Core\States\Order\Order\PartiallyShipped as OrderPartiallyShipped;
use Lunar\Core\States\Order\Order\PaymentFailed as OrderPaymentFailed;
use Lunar\Core\States\Order\Order\Refunded as OrderRefunded;
use Lunar\Core\States\Order\Order\Returned as OrderReturned;
use Lunar\Core\States\Order\Order\Shipped as OrderShipped;
use Lunar\Core\States\Order\Payment\Authorized;
use Lunar\Core\States\Order\Payment\Captured;
use Lunar\Core\States\Order\Payment\Failed as PaymentFailed;
use Lunar\Core\States\Order\Payment\Pending;
use Lunar\Core\States\Order\Payment\Refunded as PaymentRefunded;

class DefaultOrderStateConfig implements OrderStateConfig
{
    public function paymentStates(): array
    {
        return [
            Pending::class,
            Authorized::class,
            Captured::class,
            PaymentFailed::class,
            PaymentRefunded::class,
        ];
    }

    public function fulfilmentStates(): array
    {
        return [
            Unfulfilled::class,
            FulfilmentBackordered::class,
            FulfilmentProcessing::class,
            FulfilmentPartiallyShipped::class,
            FulfilmentShipped::class,
            FulfilmentDelivered::class,
            FulfilmentReturned::class,
        ];
    }

    public function orderStates(): array
    {
        return [
            AwaitingPayment::class,
            OrderPaymentFailed::class,
            OrderBackordered::class,
            InProcess::class,
            OrderPartiallyShipped::class,
            OrderShipped::class,
            OrderComplete::class,
            OrderReturned::class,
            OrderRefunded::class,
            OnHold::class,
            Cancelled::class,
        ];
    }

    public function paymentTransitions(): array
    {
        return [
            Pending::class => [Authorized::class, Captured::class, PaymentFailed::class],
            Authorized::class => [Captured::class, PaymentFailed::class],
            Captured::class => [PaymentRefunded::class],
            PaymentFailed::class => [Pending::class],
            PaymentRefunded::class => [],
        ];
    }

    public function fulfilmentTransitions(): array
    {
        return [
            Unfulfilled::class => [FulfilmentProcessing::class, FulfilmentBackordered::class],
            FulfilmentBackordered::class => [FulfilmentProcessing::class],
            FulfilmentProcessing::class => [FulfilmentShipped::class, FulfilmentPartiallyShipped::class],
            FulfilmentPartiallyShipped::class => [FulfilmentShipped::class],
            FulfilmentShipped::class => [FulfilmentDelivered::class, FulfilmentReturned::class],
            FulfilmentDelivered::class => [FulfilmentReturned::class],
            FulfilmentReturned::class => [],
        ];
    }

    public function resolveOrderState(PaymentState $payment, FulfilmentState $fulfilment): string
    {
        if ($payment instanceof PaymentRefunded) {
            return OrderRefunded::class;
        }

        $key = $payment::class.'|'.$fulfilment::class;
        $overrides = $this->overrides();

        if (isset($overrides[$key])) {
            return $overrides[$key];
        }

        return match ($payment->category()) {
            StateCategory::Failed => OrderPaymentFailed::class,
            StateCategory::Pending => AwaitingPayment::class,
            StateCategory::Active, StateCategory::Complete => match ($fulfilment->category()) {
                StateCategory::Blocked => OrderBackordered::class,
                StateCategory::Pending => InProcess::class,
                StateCategory::Active => OrderShipped::class,
                StateCategory::Complete => OrderComplete::class,
                StateCategory::Failed => OrderReturned::class,
            },
            default => AwaitingPayment::class,
        };
    }

    /**
     * @return array<string, class-string<OrderState>>
     */
    protected function overrides(): array
    {
        return [
            Captured::class.'|'.FulfilmentPartiallyShipped::class => OrderPartiallyShipped::class,
            Authorized::class.'|'.FulfilmentPartiallyShipped::class => OrderPartiallyShipped::class,
        ];
    }
}
