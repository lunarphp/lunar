<?php

use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\DefaultOrderStateConfig;
use Lunar\Core\States\Order\Order\AwaitingPayment;
use Lunar\Core\States\Order\OrderState;
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
