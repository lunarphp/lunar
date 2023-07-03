<?php

namespace Lunar\Tests\Unit\Managers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Base\DataTransferObjects\StockInfo;
use Lunar\Base\StockManagerInterface;
use Lunar\Facades\Stock;
use Lunar\Managers\StockManager;
use Lunar\Models\Cart;
use Lunar\Models\CartLine;
use Lunar\Models\ProductVariant;
use Lunar\Tests\Stubs\User;
use Lunar\Tests\TestCase;

/**
 * @group lunar.stock-manager
 */
class StockManagerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_initialise_the_manager()
    {
        $this->assertInstanceOf(
            StockManager::class,
            app(StockManagerInterface::class)
        );
    }

    /** @test */
    public function can_reserve_stock()
    {
        $stockManager = app(StockManagerInterface::class);

        $cart = Cart::factory()->create([
            'user_id' => User::factory(),
        ]);

        $data = [
            'cart_id' => $cart->id,
            'quantity' => 1,
            'purchasable_type' => ProductVariant::class,
            'purchasable_id' => ProductVariant::factory()->state([
                'stock' => 15,
                'backorder' => 3,
            ])->create()->id,
        ];

        $cartLine = CartLine::create($data);

        $this->assertTrue($stockManager->reserveStock($cartLine));
    }

    /** @test */
    public function can_reserve_stock_with_facade()
    {
        $stockManager = app(StockManagerInterface::class);

        $cart = Cart::factory()->create([
            'user_id' => User::factory(),
        ]);

        $data = [
            'cart_id' => $cart->id,
            'quantity' => 1,
            'purchasable_type' => ProductVariant::class,
            'purchasable_id' => ProductVariant::factory()->state([
                'stock' => 15,
                'backorder' => 3,
            ])->create()->id,
        ];

        $cartLine = CartLine::create($data);

        $this->assertTrue(Stock::reserveStock($cartLine));
    }

    /** @test */
    public function cannot_reserve_stock()
    {
        $stockManager = app(StockManagerInterface::class);

        $cart = Cart::factory()->create([
            'user_id' => User::factory(),
        ]);

        $data = [
            'cart_id' => $cart->id,
            'quantity' => 3,
            'purchasable_type' => ProductVariant::class,
            'purchasable_id' => ProductVariant::factory()->state([
                'stock' => 1,
                'backorder' => 0,
            ])->create()->id,
        ];

        $cartLine = CartLine::create($data);

        $this->assertFalse($stockManager->reserveStock($cartLine));
    }

    /** @test */
    public function can_release_stock()
    {
        $stockManager = app(StockManagerInterface::class);

        $cart = Cart::factory()->create([
            'user_id' => User::factory(),
        ]);

        $data = [
            'cart_id' => $cart->id,
            'quantity' => 3,
            'purchasable_type' => ProductVariant::class,
            'purchasable_id' => ProductVariant::factory()->state([
                'stock' => 15,
                'backorder' => 0,
            ])->create()->id,
        ];

        $cartLine = CartLine::create($data);

        $stockManager->reserveStock($cartLine);

        $this->assertTrue($stockManager->releaseStock($cartLine, 1));
        $this->assertTrue($stockManager->releaseStock($cartLine));
    }

    /** @test */
    public function can_dispatch_stock()
    {
        $stockManager = app(StockManagerInterface::class);

        $cart = Cart::factory()->create([
            'user_id' => User::factory(),
        ]);

        $data = [
            'cart_id' => $cart->id,
            'quantity' => 3,
            'purchasable_type' => ProductVariant::class,
            'purchasable_id' => ProductVariant::factory()->state([
                'stock' => 1,
                'backorder' => 5,
            ])->create()->id,
        ];

        $cartLine = CartLine::create($data);

        $stockManager->reserveStock($cartLine);

        $this->assertTrue($stockManager->dispatchStock($cartLine));

        $cartLine->purchasable->refresh();

        $this->assertEquals($cartLine->purchasable->stock, 0);
        $this->assertEquals($cartLine->purchasable->backorder, 3);
    }

    /** @test */
    public function can_get_stock_info()
    {
        $stockManager = app(StockManagerInterface::class);

        $cart = Cart::factory()->create([
            'user_id' => User::factory(),
        ]);

        $data = [
            'cart_id' => $cart->id,
            'quantity' => 7,
            'purchasable_type' => ProductVariant::class,
            'purchasable_id' => ProductVariant::factory()->state([
                'stock' => 5,
                'backorder' => 10,
            ])->create()->id,
        ];

        $cartLine = CartLine::create($data);

        $stockManager->reserveStock($cartLine);

        $cartLine->purchasable->refresh();

        $stockInfo = $stockManager->availableStock($cartLine->purchasable);

        $this->assertInstanceOf(StockInfo::class, $stockInfo);
        $this->assertEquals($stockInfo->stock, 0);
        $this->assertEquals($stockInfo->backorder, 8);
    }
}
