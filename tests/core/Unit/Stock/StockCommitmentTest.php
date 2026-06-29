<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Enums\StockMovementType;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\StockLevel;
use Lunar\Core\Models\StockMovement;
use Lunar\Core\States\Fulfilment\Pending;
use Lunar\Core\States\Fulfilment\Shipped;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true]);
    $this->location = Location::factory()->create(['default' => true]);
});

function placeStockTestOrder(ProductVariant $variant, int $quantity): Order
{
    $order = Order::factory()->create(['placed_at' => null]);

    OrderLine::factory()->create([
        'order_id' => $order->id,
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
        'type' => 'physical',
        'quantity' => $quantity,
    ]);

    $order->update(['placed_at' => now()]);

    return $order->refresh();
}

function shippedMovementTotal(ProductVariant $variant): int
{
    return (int) StockMovement::where('product_variant_id', $variant->id)
        ->where('type', StockMovementType::Shipped)
        ->sum('quantity');
}

test('placing an order commits stock globally and allocates it to the fulfilment location', function () {
    $variant = ProductVariant::factory()->create();
    $variant->adjustStock($this->location, 10, StockMovementType::Received);

    placeStockTestOrder($variant, 3);

    expect($variant->fresh())
        ->stock_on_hand->toBe(10)
        ->stock_committed->toBe(3)
        ->stock_available->toBe(7);

    $level = StockLevel::where('product_variant_id', $variant->id)
        ->where('location_id', $this->location->id)
        ->first();

    expect($level->committed)->toBe(3);
});

test('shipping drops on_hand and clears the commitment', function () {
    $variant = ProductVariant::factory()->create();
    $variant->adjustStock($this->location, 10, StockMovementType::Received);
    $order = placeStockTestOrder($variant, 3);

    $order->fulfilments()->first()->ship();

    expect($variant->fresh())
        ->stock_on_hand->toBe(7)
        ->stock_committed->toBe(0)
        ->stock_available->toBe(7);

    expect(shippedMovementTotal($variant))->toBe(-3);
});

test('cancelling the order releases the commitment without touching on_hand', function () {
    $variant = ProductVariant::factory()->create();
    $variant->adjustStock($this->location, 10, StockMovementType::Received);
    $order = placeStockTestOrder($variant, 3);

    $order->cancel();

    expect($variant->fresh())
        ->stock_on_hand->toBe(10)
        ->stock_committed->toBe(0)
        ->stock_available->toBe(10);
});

test('cancelling a fulfilment de-allocates the location but the order stays committed globally', function () {
    $variant = ProductVariant::factory()->create();
    $variant->adjustStock($this->location, 10, StockMovementType::Received);
    $order = placeStockTestOrder($variant, 3);

    $order->fulfilments()->first()->cancel();

    expect($variant->fresh())
        ->stock_committed->toBe(3)
        ->stock_available->toBe(7);

    $level = StockLevel::where('product_variant_id', $variant->id)
        ->where('location_id', $this->location->id)
        ->first();

    expect($level->committed)->toBe(0);
});

test('returning a shipped fulfilment puts stock back on hand', function () {
    $variant = ProductVariant::factory()->create();
    $variant->adjustStock($this->location, 10, StockMovementType::Received);
    $order = placeStockTestOrder($variant, 3);
    $fulfilment = $order->fulfilments()->first();
    $fulfilment->ship();

    $fulfilment->markReturned();

    expect($variant->fresh())
        ->stock_on_hand->toBe(10)
        ->stock_committed->toBe(0)
        ->stock_available->toBe(10);
});

test('un-shipping restores on_hand and re-commits', function () {
    $variant = ProductVariant::factory()->create();
    $variant->adjustStock($this->location, 10, StockMovementType::Received);
    $order = placeStockTestOrder($variant, 3);
    $fulfilment = $order->fulfilments()->first();
    $fulfilment->ship();

    $fulfilment->transition(Pending::class);

    expect($variant->fresh())
        ->stock_on_hand->toBe(10)
        ->stock_committed->toBe(3)
        ->stock_available->toBe(7);
});

test('undoing a return takes the stock back off hand', function () {
    $variant = ProductVariant::factory()->create();
    $variant->adjustStock($this->location, 10, StockMovementType::Received);
    $order = placeStockTestOrder($variant, 3);
    $fulfilment = $order->fulfilments()->first();
    $fulfilment->ship();
    $fulfilment->markReturned();

    $fulfilment->transition(Shipped::class);

    expect($variant->fresh()->stock_on_hand)->toBe(7);
});

test('reconcile rebuilds on_hand from the ledger and committed from open orders', function () {
    $variant = ProductVariant::factory()->create();
    $variant->adjustStock($this->location, 10, StockMovementType::Received);
    placeStockTestOrder($variant, 4);

    // Corrupt the cached rollup, then reconcile.
    $variant->forceFill([
        'stock_on_hand' => 999,
        'stock_committed' => 0,
        'stock_available' => 999,
    ])->save();

    $this->artisan('lunar:stock:reconcile')->assertSuccessful();

    expect($variant->fresh())
        ->stock_on_hand->toBe(10)
        ->stock_committed->toBe(4)
        ->stock_available->toBe(6);
});
