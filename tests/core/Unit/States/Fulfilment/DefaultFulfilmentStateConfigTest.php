<?php

use Lunar\Core\Models\Fulfilment;
use Lunar\Core\States\Fulfilment\DefaultFulfilmentStateConfig;
use Lunar\Core\States\Fulfilment\FulfilmentState;
use Lunar\Core\States\Fulfilment\Pending;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

test('the default fulfilment state is pending', function () {
    expect((new DefaultFulfilmentStateConfig)->defaultFulfilmentState())->toBe(Pending::class);
});

test('every fulfilment state extends the base and exposes a name', function () {
    $config = new DefaultFulfilmentStateConfig;

    foreach ($config->fulfilmentStates() as $class) {
        expect(is_subclass_of($class, FulfilmentState::class))->toBeTrue()
            ->and($class::$name)->toBeString()->not->toBeEmpty();
    }
});

test('every fulfilment transition references registered states', function () {
    $config = new DefaultFulfilmentStateConfig;
    $registered = $config->fulfilmentStates();

    foreach ($config->fulfilmentTransitions() as $from => $tos) {
        expect($registered)->toContain($from);

        foreach ($tos as $to) {
            expect($registered)->toContain($to);
        }
    }
});

test('every registered fulfilment state has a transition entry', function () {
    $config = new DefaultFulfilmentStateConfig;
    $transitions = $config->fulfilmentTransitions();

    foreach ($config->fulfilmentStates() as $class) {
        expect($transitions)->toHaveKey($class);
    }
});

test('every fulfilment state resolves a label', function () {
    $config = new DefaultFulfilmentStateConfig;

    foreach ($config->fulfilmentStates() as $class) {
        expect((new $class(new Fulfilment))->label())->toBeString()->not->toBeEmpty();
    }
});
