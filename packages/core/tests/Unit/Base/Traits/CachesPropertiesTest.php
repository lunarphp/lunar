<?php

namespace Lunar\Tests\Unit\Console;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\DataTypes\Price as DataTypesPrice;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Tests\TestCase;

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

        $line = $cart->lines()->create([
            'purchasable_type' => get_class($purchasable),
            'purchasable_id' => $purchasable->id,
            'quantity' => 1,
        ]);

        $manager = $cart->getManager();

        $cart = $manager->getCart();

        $this->assertInstanceOf(DataTypesPrice::class, $cart->subTotal);
        $this->assertEquals(100, $cart->subTotal->value);
        $this->assertInstanceOf(DataTypesPrice::class, $cart->total);
        $this->assertEquals(120, $cart->total->value);
        $this->assertInstanceOf(DataTypesPrice::class, $cart->taxTotal);
        $this->assertEquals(20, $cart->taxTotal->value);

        // When now fetching from the database it should automatically be hydrated...
        $cart = Cart::find($cart->id);

        $this->assertInstanceOf(DataTypesPrice::class, $cart->subTotal);
        $this->assertEquals(100, $cart->subTotal->value);
        $this->assertInstanceOf(DataTypesPrice::class, $cart->total);
        $this->assertEquals(120, $cart->total->value);
        $this->assertInstanceOf(DataTypesPrice::class, $cart->taxTotal);
        $this->assertEquals(20, $cart->taxTotal->value);
    }
}
