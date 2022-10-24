<?php

namespace Lunar\Tests\Unit\Console;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\DataTypes\Price as DataTypesPrice;
use Lunar\Managers\CartManager;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\Order;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Tests\TestCase;
use Spatie\LaravelBlink\BlinkFacade;

/**
 * @group lunar.traits.cache
 */
class CachesPropertiesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_cache_model_properties()
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

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasable),
            'purchasable_id' => $purchasable->id,
            'quantity' => 1,
        ]);

        $manager = new CartManager($cart);

        $cart = $manager->getCart();

        $this->assertInstanceOf(DataTypesPrice::class, $cart->subTotal);
        $this->assertEquals(100, $cart->subTotal->value);
        $this->assertInstanceOf(DataTypesPrice::class, $cart->total);
        $this->assertEquals(120, $cart->total->value);
        $this->assertInstanceOf(DataTypesPrice::class, $cart->taxTotal);
        $this->assertEquals(20, $cart->taxTotal->value);

        // Get the first line...
        $line = $cart->lines->first();

        $lineCart = $line->cart;

        $this->assertInstanceOf(DataTypesPrice::class, $lineCart->subTotal);
        $this->assertEquals(100, $lineCart->subTotal->value);
        $this->assertInstanceOf(DataTypesPrice::class, $lineCart->total);
        $this->assertEquals(120, $lineCart->total->value);
        $this->assertInstanceOf(DataTypesPrice::class, $lineCart->taxTotal);
        $this->assertEquals(20, $lineCart->taxTotal->value);
    }
}
