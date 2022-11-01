<?php

namespace Lunar\Tests\Unit\Actions\Carts;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Actions\Carts\AddOrUpdatePurchasable;
use Lunar\Exceptions\InvalidCartLineQuantityException;
use Lunar\Models\Cart;
use Lunar\Models\CartLine;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Tests\TestCase;

/**
 * @group lunar.actions
 * @group lunar.actions.carts
 */
class AddOrUpdatePurchasableTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function can_add_cart_lines()
    {
        $currency = Currency::factory()->create();

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
        ]);

        $purchasable = ProductVariant::factory()->create();

        Price::factory()->create([
            'price' => 100,
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasable),
            'priceable_id' => $purchasable->id,
        ]);

        $this->assertCount(0, $cart->lines);

        $action = new AddOrUpdatePurchasable;

        $action->execute($cart, $purchasable, 1);

        $this->assertCount(1, $cart->refresh()->lines);
    }

    /** @test */
    public function cannot_add_zero_quantity_line()
    {
        $currency = Currency::factory()->create();

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
        ]);

        $purchasable = ProductVariant::factory()->create();

        Price::factory()->create([
            'price' => 100,
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasable),
            'priceable_id' => $purchasable->id,
        ]);

        $this->assertCount(0, $cart->lines);

        $this->expectException(InvalidCartLineQuantityException::class);

        $action = new AddOrUpdatePurchasable;

        $action->execute($cart, $purchasable, 0);
    }

    /** @test */
    public function can_update_existing_cart_line()
    {
        $currency = Currency::factory()->create();

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
        ]);

        $purchasable = ProductVariant::factory()->create();

        Price::factory()->create([
            'price' => 100,
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasable),
            'priceable_id' => $purchasable->id,
        ]);

        $action = new AddOrUpdatePurchasable;

        $this->assertCount(0, $cart->lines);

        $action->execute($cart, $purchasable, 1);

        $this->assertCount(1, $cart->refresh()->lines);

        $action->execute($cart, $purchasable, 1);

        $this->assertCount(1, $cart->refresh()->lines);

        $this->assertDatabaseHas((new CartLine())->getTable(), [
            'cart_id' => $cart->id,
            'quantity' => 2,
        ]);
    }
}
