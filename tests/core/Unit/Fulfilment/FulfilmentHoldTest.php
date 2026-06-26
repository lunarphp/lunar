<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Fulfilment\HoldFulfilment;
use Lunar\Core\Actions\Fulfilment\ShipFulfilment;
use Lunar\Core\Exceptions\FulfilmentException;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Tests\Core\TestCase;

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

it('places a fulfilment on hold with a reason and note', function () {
    $fulfilment = $this->order->createFulfilment([$this->line->id => 2]);

    $fulfilment->hold('out-of-stock', 'Restock due Friday');

    $fulfilment->refresh();
    expect($fulfilment->isOnHold())->toBeTrue()
        ->and($fulfilment->hold_reason)->toBe('out-of-stock')
        ->and($fulfilment->hold_note)->toBe('Restock due Friday')
        // The hold is orthogonal to the lifecycle state.
        ->and((string) $fulfilment->state)->toBe('pending');
});

it('blocks shipping while on hold', function () {
    $fulfilment = $this->order->createFulfilment([$this->line->id => 2]);
    $fulfilment->hold();

    expect(ShipFulfilment::canRun($fulfilment->refresh()))->toBeFalse();

    $fulfilment->ship();
})->throws(FulfilmentException::class);

it('ships once released', function () {
    $fulfilment = $this->order->createFulfilment([$this->line->id => 2]);
    $fulfilment->hold('awaiting-payment');

    $fulfilment->refresh()->release();

    $fulfilment->refresh();
    expect($fulfilment->isOnHold())->toBeFalse()
        ->and($fulfilment->hold_reason)->toBeNull()
        ->and(ShipFulfilment::canRun($fulfilment))->toBeTrue();

    $fulfilment->ship();
    expect((string) $fulfilment->refresh()->state)->toBe('shipped');
});

it('cannot hold a shipped fulfilment', function () {
    $fulfilment = $this->order->createFulfilment([$this->line->id => 2])->ship();

    expect(HoldFulfilment::canRun($fulfilment))->toBeFalse();
});
