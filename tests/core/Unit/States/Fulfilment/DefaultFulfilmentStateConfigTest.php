<?php

use Lunar\Core\Models\Fulfilment;
use Lunar\Core\States\Fulfilment\DefaultFulfilmentStateConfig;
use Lunar\Core\States\Fulfilment\FulfilmentState;
use Lunar\Core\States\Fulfilment\Pending;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

test('the default fulfilment state is pending', function () {
    expect((app(DefaultFulfilmentStateConfig::class))->defaultFulfilmentState())->toBe(Pending::class);
});

test('every fulfilment state extends the base and exposes a name', function () {
    $config = app(DefaultFulfilmentStateConfig::class);

    foreach ($config->fulfilmentStates() as $class) {
        expect(is_subclass_of($class, FulfilmentState::class))->toBeTrue()
            ->and($class::$name)->toBeString()->not->toBeEmpty();
    }
});

test('every fulfilment transition references registered states', function () {
    $config = app(DefaultFulfilmentStateConfig::class);
    $registered = $config->fulfilmentStates();

    foreach ($config->fulfilmentTransitions() as $from => $tos) {
        expect($registered)->toContain($from);

        foreach ($tos as $to) {
            expect($registered)->toContain($to);
        }
    }
});

test('every registered fulfilment state has a transition entry', function () {
    $config = app(DefaultFulfilmentStateConfig::class);
    $transitions = $config->fulfilmentTransitions();

    foreach ($config->fulfilmentStates() as $class) {
        expect($transitions)->toHaveKey($class);
    }
});

test('every fulfilment state resolves a label', function () {
    $config = app(DefaultFulfilmentStateConfig::class);

    foreach ($config->fulfilmentStates() as $class) {
        expect((new $class(new Fulfilment))->label())->toBeString()->not->toBeEmpty();
    }
});
