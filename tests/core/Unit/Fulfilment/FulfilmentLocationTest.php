<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Exceptions\FulfilmentException;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Fulfilment;
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
});

function locationOrderLine(int $quantity = 10): array
{
    $order = Order::factory()->create();
    $line = OrderLine::factory()->create(['order_id' => $order->id, 'type' => 'physical', 'quantity' => $quantity]);

    return [$order, $line];
}

test('a created fulfilment is assigned the default location', function () {
    $location = Location::factory()->default()->create();
    [$order, $line] = locationOrderLine();

    $fulfilment = $order->createFulfilment([$line->id => 2]);

    expect($fulfilment->location_id)->toBe($location->id);
});

test('an explicit location overrides the default', function () {
    Location::factory()->default()->create();
    $other = Location::factory()->create();
    [$order, $line] = locationOrderLine();

    $fulfilment = $order->createFulfilment([$line->id => 2], ['location_id' => $other->id]);

    expect($fulfilment->location_id)->toBe($other->id);
});

test('a split parcel inherits the source location', function () {
    $location = Location::factory()->default()->create();
    [$order, $line] = locationOrderLine();
    $source = $order->createFulfilment([$line->id => 4]);

    $new = $source->split([$line->id => 1]);

    expect($new->location_id)->toBe($location->id);
});

test('fulfilments at different locations cannot be merged', function () {
    $a = Location::factory()->create();
    $b = Location::factory()->create();
    [$order, $line] = locationOrderLine();
    $target = $order->createFulfilment([$line->id => 3], ['location_id' => $a->id]);
    $source = $order->createFulfilment([$line->id => 2], ['location_id' => $b->id]);

    expect(fn () => $target->merge(Fulfilment::whereKey($source->id)->get()))
        ->toThrow(FulfilmentException::class);
});

test('fulfilments at different locations cannot have lines moved between them', function () {
    $a = Location::factory()->create();
    $b = Location::factory()->create();
    [$order, $line] = locationOrderLine();
    $from = $order->createFulfilment([$line->id => 3], ['location_id' => $a->id]);
    $to = $order->createFulfilment([$line->id => 2], ['location_id' => $b->id]);

    expect(fn () => $from->moveLinesTo($to, [$line->id => 1]))
        ->toThrow(FulfilmentException::class);
});

test('fulfilments at the same location merge normally', function () {
    $location = Location::factory()->default()->create();
    [$order, $line] = locationOrderLine();
    $target = $order->createFulfilment([$line->id => 3]);
    $source = $order->createFulfilment([$line->id => 2]);

    $target->merge(Fulfilment::whereKey($source->id)->get());

    expect($order->fulfilments()->count())->toBe(1)
        ->and($target->refresh()->lines()->first()->quantity)->toBe(5);
});

test('a pre-ship fulfilment can be moved to another location', function () {
    Location::factory()->default()->create();
    $other = Location::factory()->create();
    [$order, $line] = locationOrderLine();
    $fulfilment = $order->createFulfilment([$line->id => 2]);

    $fulfilment->changeLocation($other->id);

    expect($fulfilment->refresh()->location_id)->toBe($other->id);
});

test('a shipped fulfilment cannot change location', function () {
    Location::factory()->default()->create();
    $other = Location::factory()->create();
    [$order, $line] = locationOrderLine();
    $fulfilment = $order->createFulfilment([$line->id => 2])->ship();

    expect(fn () => $fulfilment->fresh()->changeLocation($other->id))
        ->toThrow(FulfilmentException::class);
});
