<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Fulfilment\EnsureInitialFulfilment;
use Lunar\Core\Contracts\Actions\Fulfilment\CreatesFulfilment;
use Lunar\Core\Contracts\Actions\Fulfilment\EnsuresInitialFulfilment;
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

test('placing an order creates one fulfilment covering all physical lines', function () {
    $order = Order::factory()->create(['placed_at' => null]);
    $a = OrderLine::factory()->create(['order_id' => $order->id, 'type' => 'physical', 'quantity' => 2]);
    $b = OrderLine::factory()->create(['order_id' => $order->id, 'type' => 'physical', 'quantity' => 3]);

    $order->update(['placed_at' => now()]);

    expect($order->fulfilments()->count())->toBe(1);

    $fulfilment = $order->fulfilments()->first();
    expect($fulfilment->state::$name)->toBe('pending')
        ->and($fulfilment->lines()->count())->toBe(2)
        ->and((int) $fulfilment->lines()->sum('quantity'))->toBe(5);
});

test('the initial fulfilment covers a shippable line regardless of its type', function () {
    $order = Order::factory()->create(['placed_at' => null]);
    OrderLine::factory()->create([
        'order_id' => $order->id,
        'type' => 'giftcard',
        'requires_shipping' => true,
        'quantity' => 2,
    ]);

    $order->update(['placed_at' => now()]);

    expect($order->fulfilments()->count())->toBe(1)
        ->and((int) $order->fulfilments()->first()->lines()->sum('quantity'))->toBe(2);
});

test('a digital-only order gets no fulfilment', function () {
    $order = Order::factory()->create(['placed_at' => null]);
    OrderLine::factory()->create(['order_id' => $order->id, 'type' => 'digital', 'quantity' => 1]);

    $order->update(['placed_at' => now()]);

    expect($order->fulfilments()->count())->toBe(0);
});

test('the initial fulfilment is created once and is idempotent', function () {
    $order = Order::factory()->create(['placed_at' => null]);
    OrderLine::factory()->create(['order_id' => $order->id, 'type' => 'physical', 'quantity' => 2]);

    $order->update(['placed_at' => now()]);
    $order->update(['notes' => 'touch']);
    (new EnsureInitialFulfilment(app(CreatesFulfilment::class)))->execute($order);

    expect($order->fulfilments()->count())->toBe(1);
});

test('ensure is a no-op when a fulfilment already exists', function () {
    $order = Order::factory()->create(['placed_at' => null]);
    OrderLine::factory()->create(['order_id' => $order->id, 'type' => 'physical', 'quantity' => 5]);

    $order->update(['placed_at' => now()]);
    expect($order->fulfilments()->count())->toBe(1);

    app(EnsuresInitialFulfilment::class)->execute($order);

    expect($order->fulfilments()->count())->toBe(1);
});

test('the fulfilment observers do not lazy-load when prevention is on', function () {
    $order = Order::factory()->create(['placed_at' => null]);
    $line = OrderLine::factory()->create(['order_id' => $order->id, 'type' => 'physical', 'quantity' => 4]);

    Model::preventLazyLoading(true);

    try {
        // Placement (auto-create), then a split + ship — each fires the
        // recompute observers, which must not lazy-load the parent order.
        $order->update(['placed_at' => now()]);
        $initial = $order->fulfilments()->first();
        $new = $initial->split([$line->id => 1]);
        $new->ship();
    } finally {
        Model::preventLazyLoading(false);
    }

    expect($order->fresh()->fulfilments()->count())->toBe(2);
});
