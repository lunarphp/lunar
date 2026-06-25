<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
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
    Location::factory()->default()->create();

    $this->order = Order::factory()->create();
    $this->line = OrderLine::factory()->create([
        'order_id' => $this->order->id,
        'type' => 'physical',
        'quantity' => 2,
    ]);
});

it('transitions a pending fulfilment to in progress', function () {
    $fulfilment = $this->order->createFulfilment([$this->line->id => 2]);

    $fulfilment->transition(InProgress::class);

    expect((string) $fulfilment->refresh()->state)->toBe('in-progress');
});

it('transitions an in-progress fulfilment back to pending', function () {
    $fulfilment = $this->order->createFulfilment([$this->line->id => 2]);
    $fulfilment->transition(InProgress::class);

    $fulfilment->refresh()->transition(Pending::class);

    expect((string) $fulfilment->refresh()->state)->toBe('pending');
});

it('transitions a pending fulfilment to cancelled', function () {
    $fulfilment = $this->order->createFulfilment([$this->line->id => 2]);

    $fulfilment->transition(Cancelled::class);

    expect((string) $fulfilment->refresh()->state)->toBe('cancelled');
});

it('reverts a shipped fulfilment to pending, clearing the shipment and its tracking', function () {
    $fulfilment = $this->order->createFulfilment([$this->line->id => 2])->ship([
        ['tracking_number' => 'TRK-1'],
    ]);

    expect((string) $fulfilment->state)->toBe('shipped')
        ->and($fulfilment->trackings)->toHaveCount(1)
        ->and((string) $this->order->refresh()->fulfilment_status)->toBe('fulfilled');

    $fulfilment->transition(Pending::class);

    expect((string) $fulfilment->refresh()->state)->toBe('pending')
        ->and($fulfilment->shipped_at)->toBeNull()
        ->and($fulfilment->trackings)->toHaveCount(0)
        ->and((string) $this->order->refresh()->fulfilment_status)->toBe('unfulfilled');
});

it('undoes a return back to shipped, keeping the shipment', function () {
    $fulfilment = $this->order->createFulfilment([$this->line->id => 2])->ship([
        ['tracking_number' => 'TRK-1'],
    ]);
    $shippedAt = $fulfilment->shipped_at;
    $fulfilment->refresh()->markReturned();

    expect((string) $fulfilment->refresh()->state)->toBe('returned');

    $fulfilment->transition(Shipped::class);

    expect((string) $fulfilment->refresh()->state)->toBe('shipped')
        ->and($fulfilment->shipped_at?->equalTo($shippedAt))->toBeTrue()
        ->and($fulfilment->trackings)->toHaveCount(1)
        ->and((string) $this->order->refresh()->fulfilment_status)->toBe('fulfilled');
});

it('rejects an illegal transition', function () {
    $fulfilment = $this->order->createFulfilment([$this->line->id => 2]);

    $fulfilment->transition(Returned::class);
})->throws(CouldNotPerformTransition::class);

it('exposes the states a fulfilment can move to', function () {
    $fulfilment = $this->order->createFulfilment([$this->line->id => 2]);

    $names = collect($fulfilment->state->transitionableStateInstances())
        ->map(fn ($state) => $state::$name);

    expect($names)->toContain('in-progress', 'shipped', 'cancelled')
        ->and($names)->not->toContain('returned');
});
