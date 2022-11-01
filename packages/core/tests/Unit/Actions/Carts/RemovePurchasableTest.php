<?php

namespace Lunar\Tests\Unit\Actions\Carts;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Actions\Carts\RemovePurchasable;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Tests\TestCase;

/**
 * @group lunar.actions
 * @group lunar.actions.carts
 */
class RemovePurchasableTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function can_remove_cart_line()
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

        $cart->add($purchasable, 1);

        $this->assertCount(1, $cart->refresh()->lines);

        $action = new RemovePurchasable;

        $action->execute($cart, $cart->lines->first()->id);

        $this->assertCount(0, $cart->refresh()->lines);
    }
}
