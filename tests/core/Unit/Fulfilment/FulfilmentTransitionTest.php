<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Facades\Fulfilments;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\States\Fulfilment\Cancelled;
use Lunar\Core\States\Fulfilment\InProgress;
use Lunar\Core\States\Fulfilment\Pending;
use Lunar\Core\States\Fulfilment\Returned;
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
    $fulfilment = Fulfilments::create($this->order, [$this->line->id => 2]);

    Fulfilments::transition($fulfilment, InProgress::class);

    expect((string) $fulfilment->refresh()->state)->toBe('in-progress');
});

it('transitions an in-progress fulfilment back to pending', function () {
    $fulfilment = Fulfilments::create($this->order, [$this->line->id => 2]);
    Fulfilments::transition($fulfilment, InProgress::class);

    Fulfilments::transition($fulfilment->refresh(), Pending::class);

    expect((string) $fulfilment->refresh()->state)->toBe('pending');
});

it('transitions a pending fulfilment to cancelled', function () {
    $fulfilment = Fulfilments::create($this->order, [$this->line->id => 2]);

    Fulfilments::transition($fulfilment, Cancelled::class);

    expect((string) $fulfilment->refresh()->state)->toBe('cancelled');
});

it('reverts a shipped fulfilment to pending, un-ships it and frees its items', function () {
    $fulfilment = Fulfilments::ship(Fulfilments::create($this->order, [$this->line->id => 2]));

    expect((string) $fulfilment->state)->toBe('shipped')
        ->and((string) $this->order->refresh()->fulfilment_status)->toBe('fulfilled');

    Fulfilments::transition($fulfilment, Pending::class);

    expect((string) $fulfilment->refresh()->state)->toBe('pending')
        ->and($fulfilment->shipped_at)->toBeNull()
        ->and((string) $this->order->refresh()->fulfilment_status)->toBe('unfulfilled');
});

it('rejects an illegal transition', function () {
    $fulfilment = Fulfilments::create($this->order, [$this->line->id => 2]);

    Fulfilments::transition($fulfilment, Returned::class);
})->throws(CouldNotPerformTransition::class);

it('exposes the states a fulfilment can move to', function () {
    $fulfilment = Fulfilments::create($this->order, [$this->line->id => 2]);

    $names = collect($fulfilment->state->transitionableStateInstances())
        ->map(fn ($state) => $state::$name);

    expect($names)->toContain('in-progress', 'shipped', 'cancelled')
        ->and($names)->not->toContain('returned');
});
