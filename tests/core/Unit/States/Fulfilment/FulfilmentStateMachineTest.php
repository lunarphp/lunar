<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\Language;
use Lunar\Core\States\Fulfilment\Cancelled;
use Lunar\Core\States\Fulfilment\InProgress;
use Lunar\Core\States\Fulfilment\Pending;
use Lunar\Core\States\Fulfilment\Returned;
use Lunar\Core\States\Fulfilment\Shipped;
use Lunar\Tests\Core\TestCase;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true]);
});

test('a new fulfilment defaults to pending', function () {
    $fulfilment = Fulfilment::factory()->create();

    expect($fulfilment->state)->toBeInstanceOf(Pending::class);
});

test('a fulfilment transitions through the lifecycle', function () {
    $fulfilment = Fulfilment::factory()->create(['state' => 'pending']);

    $fulfilment->state->transitionTo(InProgress::class);
    expect((string) $fulfilment->fresh()->state)->toBe('in-progress');

    $fulfilment->refresh()->state->transitionTo(Shipped::class);
    expect((string) $fulfilment->fresh()->state)->toBe('shipped');

    $fulfilment->refresh()->state->transitionTo(Returned::class);
    expect((string) $fulfilment->fresh()->state)->toBe('returned');
});

test('cancelled is terminal', function () {
    $fulfilment = Fulfilment::factory()->create(['state' => 'cancelled']);

    expect(fn () => $fulfilment->state->transitionTo(Shipped::class))
        ->toThrow(CouldNotPerformTransition::class);
});

test('a shipped fulfilment cannot be cancelled', function () {
    $fulfilment = Fulfilment::factory()->create(['state' => 'shipped']);

    expect(fn () => $fulfilment->state->transitionTo(Cancelled::class))
        ->toThrow(CouldNotPerformTransition::class);
});

test('a pending fulfilment can ship directly', function () {
    $fulfilment = Fulfilment::factory()->create(['state' => 'pending']);

    $fulfilment->state->transitionTo(Shipped::class);

    expect((string) $fulfilment->fresh()->state)->toBe('shipped');
});
