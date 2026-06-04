<?php

use Lunar\Core\Enums\OrderStateCategory;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\DefaultOrderStateConfig;
use Lunar\Core\States\Order\Fulfilment\Fulfilled;
use Lunar\Core\States\Order\Fulfilment\PartiallyFulfilled;
use Lunar\Core\States\Order\Fulfilment\PartiallyReturned;
use Lunar\Core\States\Order\Fulfilment\Returned;
use Lunar\Core\States\Order\Fulfilment\Unfulfilled;
use Lunar\Core\States\Order\Order\AwaitingPayment;
use Lunar\Core\States\Order\OrderState;
use Lunar\Core\States\Order\Payment\Authorized;
use Lunar\Core\States\Order\Payment\Paid;
use Lunar\Core\States\Order\Payment\PartiallyPaid;
use Lunar\Core\States\Order\Payment\PartiallyRefunded;
use Lunar\Core\States\Order\Payment\Pending;
use Lunar\Core\States\Order\Payment\Refunded;
use Lunar\Core\States\Order\Payment\Voided;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

test('the default state is awaiting payment', function () {
    expect((new DefaultOrderStateConfig)->defaultOrderState())->toBe(AwaitingPayment::class);
});

test('every order state extends the OrderState base and exposes a name', function () {
    $config = new DefaultOrderStateConfig;

    foreach ($config->orderStates() as $class) {
        expect(is_subclass_of($class, OrderState::class))->toBeTrue()
            ->and($class::$name)->toBeString()->not->toBeEmpty();
    }
});

test('every transition references registered states', function () {
    $config = new DefaultOrderStateConfig;
    $registered = $config->orderStates();

    foreach ($config->orderTransitions() as $from => $tos) {
        expect($registered)->toContain($from);

        foreach ($tos as $to) {
            expect($registered)->toContain($to);
        }
    }
});

test('every registered state has a transition entry', function () {
    $config = new DefaultOrderStateConfig;
    $transitions = $config->orderTransitions();

    foreach ($config->orderStates() as $class) {
        expect($transitions)->toHaveKey($class);
    }
});

test('every order state resolves a label', function () {
    $config = new DefaultOrderStateConfig;

    foreach ($config->orderStates() as $class) {
        expect((new $class(new Order))->label())->toBeString()->not->toBeEmpty();
    }
});

test('every order state resolves a category', function () {
    $config = new DefaultOrderStateConfig;

    foreach ($config->orderStates() as $class) {
        expect((new $class(new Order))->category())->toBeInstanceOf(OrderStateCategory::class);
    }
});

test('Backordered is no longer registered', function () {
    $config = new DefaultOrderStateConfig;

    expect(class_exists('Lunar\\Core\\States\\Order\\Order\\Backordered'))->toBeFalse()
        ->and($config->orderTransitions())->not->toHaveKey('Lunar\\Core\\States\\Order\\Order\\Backordered');
});

test('every derived order status is reachable in the transition graph', function () {
    $config = new DefaultOrderStateConfig;

    // Every state the graph can transition INTO (plus the default entry point).
    $inbound = collect($config->orderTransitions())
        ->flatten()
        ->push($config->defaultOrderState())
        ->unique();

    $paymentStates = [
        Pending::class,
        Authorized::class,
        PartiallyPaid::class,
        Paid::class,
        PartiallyRefunded::class,
        Refunded::class,
        Voided::class,
    ];

    $fulfilmentStates = [
        Unfulfilled::class,
        PartiallyFulfilled::class,
        Fulfilled::class,
        PartiallyReturned::class,
        Returned::class,
    ];

    // Run the resolver across the full payment × fulfilment matrix.
    foreach ($paymentStates as $payment) {
        foreach ($fulfilmentStates as $fulfilment) {
            $derived = $config->computeOrderStatus(new $payment(new Order), new $fulfilment(new Order));

            expect($inbound)->toContain($derived);
        }
    }
});
