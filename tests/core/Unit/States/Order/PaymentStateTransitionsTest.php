<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\Payment\Authorized;
use Lunar\Core\States\Order\Payment\Captured;
use Lunar\Core\States\Order\Payment\Failed;
use Lunar\Core\States\Order\Payment\Pending;
use Lunar\Core\States\Order\Payment\Refunded;
use Lunar\Tests\Core\TestCase;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true]);
});

test('default payment state is Pending', function () {
    $order = Order::factory()->create();
    expect($order->payment_status)->toBeInstanceOf(Pending::class);
});

$allowed = [
    [Pending::class, Authorized::class],
    [Pending::class, Captured::class],
    [Pending::class, Failed::class],
    [Authorized::class, Captured::class],
    [Authorized::class, Failed::class],
    [Captured::class, Refunded::class],
    [Failed::class, Pending::class],
];

foreach ($allowed as [$from, $to]) {
    $fromName = $from::$name;
    $toName = $to::$name;
    test("allowed: payment {$fromName} → {$toName}", function () use ($from, $to) {
        $order = Order::factory()->create(['payment_status' => $from::$name]);
        $order->payment_status->transitionTo($to);
        expect($order->fresh()->payment_status)->toBeInstanceOf($to);
    });
}

test('cannot transition Pending directly to Refunded', function () {
    $order = Order::factory()->create(['payment_status' => Pending::$name]);
    expect(fn () => $order->payment_status->transitionTo(Refunded::class))
        ->toThrow(CouldNotPerformTransition::class);
});

test('cannot transition Refunded to anything', function () {
    $order = Order::factory()->create(['payment_status' => Refunded::$name]);
    expect(fn () => $order->payment_status->transitionTo(Pending::class))
        ->toThrow(CouldNotPerformTransition::class);
});
