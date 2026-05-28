<?php

use Lunar\Core\Enums\StateCategory;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\DefaultOrderStateConfig;
use Lunar\Core\States\Order\Fulfilment\Backordered as FulfilmentBackordered;
use Lunar\Core\States\Order\Fulfilment\Delivered as FulfilmentDelivered;
use Lunar\Core\States\Order\Fulfilment\PartiallyShipped as FulfilmentPartiallyShipped;
use Lunar\Core\States\Order\Fulfilment\Processing as FulfilmentProcessing;
use Lunar\Core\States\Order\Fulfilment\Returned as FulfilmentReturned;
use Lunar\Core\States\Order\Fulfilment\Shipped as FulfilmentShipped;
use Lunar\Core\States\Order\Fulfilment\Unfulfilled;
use Lunar\Core\States\Order\FulfilmentState;
use Lunar\Core\States\Order\Order\AwaitingPayment;
use Lunar\Core\States\Order\Order\Backordered as OrderBackordered;
use Lunar\Core\States\Order\Order\Complete as OrderComplete;
use Lunar\Core\States\Order\Order\InProcess;
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
use Lunar\Core\States\Order\PaymentState;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

function payment(string $class): PaymentState
{
    return new $class(new Order);
}

function fulfilment(string $class): FulfilmentState
{
    return new $class(new Order);
}

$cases = [
    [Pending::class, Unfulfilled::class, AwaitingPayment::class],
    [PaymentFailed::class, Unfulfilled::class, OrderPaymentFailed::class],
    [PaymentFailed::class, FulfilmentProcessing::class, OrderPaymentFailed::class],
    [Authorized::class, FulfilmentBackordered::class, OrderBackordered::class],
    [Captured::class, FulfilmentBackordered::class, OrderBackordered::class],
    [Captured::class, Unfulfilled::class, InProcess::class],
    [Captured::class, FulfilmentProcessing::class, InProcess::class],
    [Captured::class, FulfilmentShipped::class, OrderShipped::class],
    [Captured::class, FulfilmentPartiallyShipped::class, OrderPartiallyShipped::class],
    [Authorized::class, FulfilmentPartiallyShipped::class, OrderPartiallyShipped::class],
    [Captured::class, FulfilmentDelivered::class, OrderComplete::class],
    [Captured::class, FulfilmentReturned::class, OrderReturned::class],
    [PaymentRefunded::class, Unfulfilled::class, OrderRefunded::class],
    [PaymentRefunded::class, FulfilmentShipped::class, OrderRefunded::class],
];

foreach ($cases as [$paymentClass, $fulfilmentClass, $expectedOrderClass]) {
    $label = sprintf('%s + %s → %s', $paymentClass::$name, $fulfilmentClass::$name, $expectedOrderClass::$name);

    test("resolver: {$label}", function () use ($paymentClass, $fulfilmentClass, $expectedOrderClass) {
        $config = new DefaultOrderStateConfig;
        expect($config->resolveOrderState(payment($paymentClass), fulfilment($fulfilmentClass)))
            ->toBe($expectedOrderClass);
    });
}

test('every payment state declares a StateCategory', function () {
    $config = new DefaultOrderStateConfig;

    foreach ($config->paymentStates() as $class) {
        $state = payment($class);
        expect($state->category())->toBeInstanceOf(StateCategory::class);
    }
});

test('every fulfilment state declares a StateCategory', function () {
    $config = new DefaultOrderStateConfig;

    foreach ($config->fulfilmentStates() as $class) {
        $state = fulfilment($class);
        expect($state->category())->toBeInstanceOf(StateCategory::class);
    }
});
