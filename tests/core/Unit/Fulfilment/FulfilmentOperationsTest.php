<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Exceptions\FulfilmentException;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\States\Fulfilment\Cancelled;
use Lunar\Core\States\Fulfilment\InProgress;
use Lunar\Core\States\Fulfilment\Returned;
use Lunar\Core\States\Fulfilment\Shipped;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true]);
});

function orderWithLine(int $quantity = 10): array
{
    $order = Order::factory()->create();
    $line = OrderLine::factory()->create(['order_id' => $order->id, 'type' => 'physical', 'quantity' => $quantity]);

    return [$order, $line];
}

test('create fulfilment writes lines and defaults to pending', function () {
    [$order, $line] = orderWithLine(10);

    $fulfilment = $order->createFulfilment([$line->id => 4]);

    expect($fulfilment->state::$name)->toBe('pending')
        ->and($fulfilment->lines)->toHaveCount(1)
        ->and($fulfilment->lines->first()->quantity)->toBe(4);
});

test('a line that does not require shipping cannot be fulfilled', function () {
    $order = Order::factory()->create();
    $line = OrderLine::factory()->create(['order_id' => $order->id, 'type' => 'digital', 'quantity' => 2]);

    expect(fn () => $order->createFulfilment([$line->id => 1]))
        ->toThrow(FulfilmentException::class);
});

test('create fulfilment rejects more than the line quantity', function () {
    [$order, $line] = orderWithLine(3);

    expect(fn () => $order->createFulfilment([$line->id => 4]))
        ->toThrow(FulfilmentException::class);
});

test('create fulfilment rejects exceeding the outstanding quantity across parcels', function () {
    [$order, $line] = orderWithLine(5);
    $order->createFulfilment([$line->id => 3]);

    expect(fn () => $order->createFulfilment([$line->id => 3]))
        ->toThrow(FulfilmentException::class);
});

test('ship fulfilment stamps shipped_at and records multiple trackings', function () {
    [$order, $line] = orderWithLine(10);
    $fulfilment = $order->createFulfilment([$line->id => 4]);

    $shipped = $fulfilment->ship([
        ['tracking_number' => 'TRACK123', 'shipping_method' => 'Royal Mail'],
        ['tracking_number' => 'TRACK456', 'shipping_method' => 'DPD'],
    ]);

    expect($shipped->state)->toBeInstanceOf(Shipped::class)
        ->and($shipped->shipped_at)->not->toBeNull()
        ->and($shipped->trackings)->toHaveCount(2)
        ->and($shipped->trackings->pluck('tracking_number')->all())->toBe(['TRACK123', 'TRACK456']);
});

test('ship accepts a single tracking entry', function () {
    [$order, $line] = orderWithLine(10);
    $shipped = $order->createFulfilment([$line->id => 4])->ship([
        'tracking_number' => 'SINGLE-1',
    ]);

    expect($shipped->trackings)->toHaveCount(1)
        ->and($shipped->trackings->first()->tracking_number)->toBe('SINGLE-1');
});

test('add tracking appends a tracking reference to a fulfilment', function () {
    [$order, $line] = orderWithLine(10);
    $fulfilment = $order->createFulfilment([$line->id => 4])->ship(['tracking_number' => 'A']);

    $fulfilment->addTracking(['tracking_number' => 'B', 'shipping_method' => 'Express']);

    expect($fulfilment->fresh()->trackings)->toHaveCount(2);
});

test('split moves outstanding quantity into a new parcel', function () {
    [$order, $line] = orderWithLine(10);
    $source = $order->createFulfilment([$line->id => 10]);

    $new = $source->split([$line->id => 4]);

    expect($new->lines->first()->quantity)->toBe(4)
        ->and($source->fresh()->lines->first()->quantity)->toBe(6)
        ->and($order->fulfilments()->count())->toBe(2);
});

test('split inherits the source parcel state', function () {
    [$order, $line] = orderWithLine(10);
    $source = $order->createFulfilment([$line->id => 10]);
    $source->transition(InProgress::class);

    $new = $source->fresh()->split([$line->id => 4]);

    expect($new->state::$name)->toBe('in-progress');
});

test('a shipped fulfilment cannot be split', function () {
    [$order, $line] = orderWithLine(10);
    $source = $order->createFulfilment([$line->id => 10]);
    $source->ship();

    expect(fn () => $source->fresh()->split([$line->id => 4]))
        ->toThrow(FulfilmentException::class);
});

test('merge folds source parcels into the target', function () {
    [$order, $line] = orderWithLine(10);
    $target = $order->createFulfilment([$line->id => 3]);
    $source = $order->createFulfilment([$line->id => 2]);

    $merged = $target->merge(Fulfilment::whereKey($source->id)->get());

    expect($merged->fresh()->lines->first()->quantity)->toBe(5)
        ->and($order->fulfilments()->count())->toBe(1);
});

test('cancel returns quantities to the unfulfilled pool', function () {
    [$order, $line] = orderWithLine(5);
    $fulfilment = $order->createFulfilment([$line->id => 5]);

    $cancelled = $fulfilment->cancel();

    expect($cancelled->state)->toBeInstanceOf(Cancelled::class);

    // The cancelled parcel no longer counts, so the full quantity is fulfillable again.
    $replacement = $order->createFulfilment([$line->id => 5]);
    expect($replacement->lines->first()->quantity)->toBe(5);
});

test('return marks a shipped fulfilment as returned', function () {
    [$order, $line] = orderWithLine(5);
    $fulfilment = $order->createFulfilment([$line->id => 5]);
    $fulfilment->ship();

    $returned = $fulfilment->fresh()->markReturned();

    expect($returned->state)->toBeInstanceOf(Returned::class);
});

test('move transfers selected line quantities into another parcel', function () {
    [$order, $line] = orderWithLine(10);
    $from = $order->createFulfilment([$line->id => 6]);
    $to = $order->createFulfilment([$line->id => 4]);

    $from->moveLinesTo($to, [$line->id => 2]);

    expect($from->fresh()->lines()->first()->quantity)->toBe(4)
        ->and($to->fresh()->lines()->first()->quantity)->toBe(6)
        ->and($order->fulfilments()->count())->toBe(2);
});

test('moving every line removes the now-empty source parcel', function () {
    [$order, $line] = orderWithLine(10);
    $from = $order->createFulfilment([$line->id => 3]);
    $to = $order->createFulfilment([$line->id => 7]);

    $from->moveLinesTo($to, [$line->id => 3]);

    expect($order->fulfilments()->count())->toBe(1)
        ->and($to->fresh()->lines()->first()->quantity)->toBe(10)
        ->and(Fulfilment::find($from->id))->toBeNull();
});

test('move rejects more than the source line carries', function () {
    [$order, $line] = orderWithLine(10);
    $from = $order->createFulfilment([$line->id => 2]);
    $to = $order->createFulfilment([$line->id => 2]);

    expect(fn () => $from->moveLinesTo($to, [$line->id => 5]))
        ->toThrow(FulfilmentException::class);
});
