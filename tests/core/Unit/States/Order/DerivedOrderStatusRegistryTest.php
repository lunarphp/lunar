<?php

use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\Fulfilment\Fulfilled;
use Lunar\Core\States\Order\Fulfilment\FulfilmentStatus;
use Lunar\Core\States\Order\Fulfilment\PartiallyFulfilled;
use Lunar\Core\States\Order\Fulfilment\PartiallyReturned;
use Lunar\Core\States\Order\Fulfilment\Returned as FulfilmentReturned;
use Lunar\Core\States\Order\Fulfilment\Unfulfilled;
use Lunar\Core\States\Order\Payment\Authorized;
use Lunar\Core\States\Order\Payment\Paid;
use Lunar\Core\States\Order\Payment\PartiallyPaid;
use Lunar\Core\States\Order\Payment\PartiallyRefunded;
use Lunar\Core\States\Order\Payment\PaymentStatus;
use Lunar\Core\States\Order\Payment\Pending;
use Lunar\Core\States\Order\Payment\Refunded as PaymentRefunded;
use Lunar\Core\States\Order\Payment\Voided;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

test('the payment status registry pins its default and registered states', function () {
    $config = PaymentStatus::config();

    expect($config->defaultStateClass)->toBe(Pending::class)
        ->and($config->registeredStates)->toEqualCanonicalizing([
            Pending::class,
            Authorized::class,
            PartiallyPaid::class,
            Paid::class,
            PartiallyRefunded::class,
            PaymentRefunded::class,
            Voided::class,
        ]);
});

test('the fulfilment status registry pins its default and registered states', function () {
    $config = FulfilmentStatus::config();

    expect($config->defaultStateClass)->toBe(Unfulfilled::class)
        ->and($config->registeredStates)->toEqualCanonicalizing([
            Unfulfilled::class,
            PartiallyFulfilled::class,
            Fulfilled::class,
            PartiallyReturned::class,
            FulfilmentReturned::class,
        ]);
});

test('every derived order status exposes a name and a label', function () {
    $order = new Order;

    $states = [
        ...PaymentStatus::config()->registeredStates,
        ...FulfilmentStatus::config()->registeredStates,
    ];

    foreach ($states as $class) {
        expect($class::$name)->toBeString()->not->toBeEmpty()
            ->and((new $class($order))->label())->toBeString()->not->toBeEmpty();
    }
});
