<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Exceptions\FulfilmentException;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true]);
});

test('an order line cannot be reduced below the fulfilled quantity', function () {
    $order = Order::factory()->create();
    $line = OrderLine::factory()->create(['order_id' => $order->id, 'type' => 'physical', 'quantity' => 10]);

    $order->createFulfilment([$line->id => 6])->ship();

    expect(fn () => $line->update(['quantity' => 4]))
        ->toThrow(FulfilmentException::class);
});

test('an order line can be reduced down to the fulfilled floor', function () {
    $order = Order::factory()->create();
    $line = OrderLine::factory()->create(['order_id' => $order->id, 'type' => 'physical', 'quantity' => 10]);

    $order->createFulfilment([$line->id => 6])->ship();

    $line->update(['quantity' => 6]);

    expect($line->fresh()->quantity)->toBe(6);
});

test('a line with no fulfilments can be freely reduced', function () {
    $order = Order::factory()->create();
    $line = OrderLine::factory()->create(['order_id' => $order->id, 'type' => 'physical', 'quantity' => 10]);

    $line->update(['quantity' => 1]);

    expect($line->fresh()->quantity)->toBe(1);
});
