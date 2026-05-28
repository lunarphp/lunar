<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\Fulfilment\Backordered;
use Lunar\Core\States\Order\Fulfilment\Delivered;
use Lunar\Core\States\Order\Fulfilment\PartiallyShipped;
use Lunar\Core\States\Order\Fulfilment\Processing;
use Lunar\Core\States\Order\Fulfilment\Returned;
use Lunar\Core\States\Order\Fulfilment\Shipped;
use Lunar\Core\States\Order\Fulfilment\Unfulfilled;
use Lunar\Tests\Core\TestCase;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true]);
});

test('default fulfilment state is Unfulfilled', function () {
    $order = Order::factory()->create();
    expect($order->fulfilment_status)->toBeInstanceOf(Unfulfilled::class);
});

$allowed = [
    [Unfulfilled::class, Processing::class],
    [Unfulfilled::class, Backordered::class],
    [Backordered::class, Processing::class],
    [Processing::class, Shipped::class],
    [Processing::class, PartiallyShipped::class],
    [PartiallyShipped::class, Shipped::class],
    [Shipped::class, Delivered::class],
    [Shipped::class, Returned::class],
    [Delivered::class, Returned::class],
];

foreach ($allowed as [$from, $to]) {
    $fromName = $from::$name;
    $toName = $to::$name;
    test("allowed: fulfilment {$fromName} → {$toName}", function () use ($from, $to) {
        $order = Order::factory()->create([
            'fulfilment_status' => $from::$name,
            'payment_status' => 'captured',
        ]);
        $order->fulfilment_status->transitionTo($to);
        expect($order->fresh()->fulfilment_status)->toBeInstanceOf($to);
    });
}

test('cannot transition Unfulfilled directly to Shipped', function () {
    $order = Order::factory()->create(['fulfilment_status' => Unfulfilled::$name]);
    expect(fn () => $order->fulfilment_status->transitionTo(Shipped::class))
        ->toThrow(CouldNotPerformTransition::class);
});

test('cannot transition Returned to anything', function () {
    $order = Order::factory()->create(['fulfilment_status' => Returned::$name]);
    expect(fn () => $order->fulfilment_status->transitionTo(Unfulfilled::class))
        ->toThrow(CouldNotPerformTransition::class);
});
