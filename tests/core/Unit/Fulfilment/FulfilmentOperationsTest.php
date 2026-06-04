<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Exceptions\FulfilmentException;
use Lunar\Core\Facades\Fulfilments;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\States\Fulfilment\Cancelled;
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

    $fulfilment = Fulfilments::create($order, [$line->id => 4]);

    expect($fulfilment->state::$name)->toBe('pending')
        ->and($fulfilment->lines)->toHaveCount(1)
        ->and($fulfilment->lines->first()->quantity)->toBe(4);
});

test('create fulfilment rejects more than the line quantity', function () {
    [$order, $line] = orderWithLine(3);

    expect(fn () => Fulfilments::create($order, [$line->id => 4]))
        ->toThrow(FulfilmentException::class);
});

test('create fulfilment rejects exceeding the outstanding quantity across parcels', function () {
    [$order, $line] = orderWithLine(5);
    Fulfilments::create($order, [$line->id => 3]);

    expect(fn () => Fulfilments::create($order, [$line->id => 3]))
        ->toThrow(FulfilmentException::class);
});

test('ship fulfilment stamps shipped_at and records tracking', function () {
    [$order, $line] = orderWithLine(10);
    $fulfilment = Fulfilments::create($order, [$line->id => 4]);

    $shipped = Fulfilments::ship($fulfilment, [
        'tracking_number' => 'TRACK123',
        'shipping_method' => 'Royal Mail',
    ]);

    expect($shipped->state)->toBeInstanceOf(Shipped::class)
        ->and($shipped->shipped_at)->not->toBeNull()
        ->and($shipped->tracking_number)->toBe('TRACK123')
        ->and($shipped->shipping_method)->toBe('Royal Mail');
});

test('split moves outstanding quantity into a new parcel', function () {
    [$order, $line] = orderWithLine(10);
    $source = Fulfilments::create($order, [$line->id => 10]);

    $new = Fulfilments::split($source, [$line->id => 4]);

    expect($new->lines->first()->quantity)->toBe(4)
        ->and($source->fresh()->lines->first()->quantity)->toBe(6)
        ->and($order->fulfilments()->count())->toBe(2);
});

test('a shipped fulfilment cannot be split', function () {
    [$order, $line] = orderWithLine(10);
    $source = Fulfilments::create($order, [$line->id => 10]);
    Fulfilments::ship($source);

    expect(fn () => Fulfilments::split($source->fresh(), [$line->id => 4]))
        ->toThrow(FulfilmentException::class);
});

test('merge folds source parcels into the target', function () {
    [$order, $line] = orderWithLine(10);
    $target = Fulfilments::create($order, [$line->id => 3]);
    $source = Fulfilments::create($order, [$line->id => 2]);

    $merged = Fulfilments::merge($target, Fulfilment::whereKey($source->id)->get());

    expect($merged->fresh()->lines->first()->quantity)->toBe(5)
        ->and($order->fulfilments()->count())->toBe(1);
});

test('merge errors on conflicting tracking', function () {
    [$order, $line] = orderWithLine(10);
    $target = Fulfilments::create($order, [$line->id => 3], ['tracking_number' => 'AAA']);
    $source = Fulfilments::create($order, [$line->id => 2], ['tracking_number' => 'BBB']);

    expect(fn () => Fulfilments::merge($target, Fulfilment::whereKey($source->id)->get()))
        ->toThrow(FulfilmentException::class);
});

test('cancel returns quantities to the unfulfilled pool', function () {
    [$order, $line] = orderWithLine(5);
    $fulfilment = Fulfilments::create($order, [$line->id => 5]);

    $cancelled = Fulfilments::cancel($fulfilment);

    expect($cancelled->state)->toBeInstanceOf(Cancelled::class);

    // The cancelled parcel no longer counts, so the full quantity is fulfillable again.
    $replacement = Fulfilments::create($order, [$line->id => 5]);
    expect($replacement->lines->first()->quantity)->toBe(5);
});

test('return marks a shipped fulfilment as returned', function () {
    [$order, $line] = orderWithLine(5);
    $fulfilment = Fulfilments::create($order, [$line->id => 5]);
    Fulfilments::ship($fulfilment);

    $returned = Fulfilments::return($fulfilment->fresh());

    expect($returned->state)->toBeInstanceOf(Returned::class);
});
