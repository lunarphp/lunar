<?php

namespace Lunar\Tests\Unit\Pipelines\Order\Creation;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Exceptions\InsufficientStockException;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;
use Lunar\Models\Product;
use Lunar\Models\ProductType;
use Lunar\Models\ProductVariant;
use Lunar\Pipelines\Order\Creation\DeductStockFromInventory;
use Lunar\Tests\TestCase;

/**
 * @group lunar.orders.pipelines
 */
class DeductStockFromInventoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_run_pipeline()
    {
        $orderLines = OrderLine::factory(5)->make();

        $order = Order::factory()->withLines($orderLines)->create();

        app(DeductStockFromInventory::class)->handle($order, function ($order) {
            return $order;
        });

        $this->assertNotNull($order->reference);
    }

    /** @test */
    public function it_should_deduct_stock_from_inventory_when_available()
    {
        // Given
        $product = Product::factory()->create();

        $productVariant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'purchasable' => 'in_stock',
            'stock' => 10
        ]);

        $orderLines = OrderLine::factory(1)->make([
            'purchasable_type' => ProductVariant::class,
            'purchasable_id' => $productVariant->id,
            'quantity' => 5,
        ]);

        $order = Order::factory()->withLines($orderLines)->create();

        // When
        app(DeductStockFromInventory::class)->handle($order, function ($order) {
            return $order;
        });

        $productVariant->refresh();

        // Then
        $this->assertEquals(5, $productVariant->stock);
    }

    /** @test */
    public function it_should_throw_exception_when_amount_is_unavailable_and_purchasability_is_in_stock()
    {
        // Given
        $product = Product::factory()->create();

        $productVariant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'purchasable' => 'in_stock',
            'stock' => 0
        ]);

        $orderLines = OrderLine::factory(1)->make([
            'purchasable_type' => ProductVariant::class,
            'purchasable_id' => $productVariant->id,
            'quantity' => 1,
        ]);

        $order = Order::factory()->withLines($orderLines)->create();

        // Then
        $this->expectException(InsufficientStockException::class);
        $this->expectExceptionMessage('Insufficient stock for product variant with EAN ' . $productVariant->ean);

        // When
        app(DeductStockFromInventory::class)->handle($order, function ($order) {
            return $order;
        });

        $productVariant->refresh();
    }

    /** @test */
    public function it_should_not_throw_exception_when_purchasable_is_always()
    {
        // Given
        $product = Product::factory()->create();

        $productVariant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'purchasable' => 'always',
            'stock' => 0
        ]);

        $orderLines = OrderLine::factory(1)->make([
            'purchasable_type' => ProductVariant::class,
            'purchasable_id' => $productVariant->id,
            'quantity' => 1,
        ]);

        $order = Order::factory()->withLines($orderLines)->create();

        // When
        app(DeductStockFromInventory::class)->handle($order, function ($order) {
            return $order;
        });

        $productVariant->refresh();

        // Then
        $this->assertEquals(-1, $productVariant->stock);
    }

    /** @test */
    public function it_should_deduct_from_stock_when_purchasability_is_backorder_and_backorder_is_placed()
    {
        // Given
        $product = Product::factory()->create();

        $productVariant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'purchasable' => 'backorders',
            'stock' => 0,
            'backorder' => 1
        ]);

        $orderLines = OrderLine::factory(1)->make([
            'purchasable_type' => ProductVariant::class,
            'purchasable_id' => $productVariant->id,
            'quantity' => 1,
        ]);

        $order = Order::factory()->withLines($orderLines)->create();

        // When
        app(DeductStockFromInventory::class)->handle($order, function ($order) {
            return $order;
        });

        $productVariant->refresh();

        // Then
        $this->assertEquals(-1, $productVariant->stock);
        $this->assertEquals(1, $productVariant->backorder);
    }
}
