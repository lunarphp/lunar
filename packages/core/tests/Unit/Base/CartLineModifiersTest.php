<?php

namespace GetCandy\Tests\Unit\Base;

use GetCandy\Base\CartLineModifier;
use GetCandy\Base\CartLineModifiers;
use GetCandy\DataTypes\Price as DataTypesPrice;
use GetCandy\Models\Cart;
use GetCandy\Models\CartLine;
use GetCandy\Models\Currency;
use GetCandy\Models\Language;
use GetCandy\Models\Price;
use GetCandy\Models\ProductVariant;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pipeline\Pipeline;

/**
 * @group modifiers
 */
class CartLineModifiersTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Language::factory()->create([
            'default' => true,
            'code'    => 'en',
        ]);

        Currency::factory()->create([
            'default'        => true,
            'decimal_places' => 2,
        ]);
    }

    /** @test */
    public function can_add_modifiers()
    {
        $modifiers = app(CartLineModifiers::class);

        $modifiers->add(new class extends CartLineModifier
        {
            public function calculating(CartLine $cartLine)
            {
                echo 1;
            }
        });

        $this->assertCount(1, $modifiers->getModifiers());
    }

    /** @test */
    public function can_send_modifiers_through_pipeline()
    {
        $modifiers = app(CartLineModifiers::class);

        $currency = Currency::factory()->create();

        $modifiers->add(new class extends CartLineModifier
        {
            public function calculating(CartLine $cartLine)
            {
                $cartLine->total = new DataTypesPrice(
                    100,
                    Currency::getDefault()
                );
            }
        });

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
        ]);

        $purchasable = ProductVariant::factory()->create();

        Price::factory()->create([
            'price'          => 100,
            'tier'           => 1,
            'currency_id'    => $currency->id,
            'priceable_type' => get_class($purchasable),
            'priceable_id'   => $purchasable->id,
        ]);

        $cart->lines()->createMany([
            ['quantity' => 1, 'purchasable_type' => ProductVariant::class, 'purchasable_id' => $purchasable->id],
        ]);

        $cartLine = $cart->lines->first();

        $this->assertEquals(0, $cartLine->total?->value);

        app(Pipeline::class)
            ->through(
                $modifiers->getModifiers()->toArray()
            )->send($cartLine)
            ->via('processCalculating')
            ->thenReturn();

        $this->assertEquals(100, $cartLine->total?->value);
    }

    /** @test */
    public function can_send_multiple_modifiers_through_pipeline()
    {
        $modifiers = app(CartLineModifiers::class);

        $currency = Currency::factory()->create();

        $modifiers->add(new class extends CartLineModifier
        {
            public function calculating(CartLine $cartLine)
            {
                $cartLine->total = new DataTypesPrice(
                    100,
                    Currency::getDefault()
                );
            }
        });

        $modifiers->add(new class extends CartLineModifier
        {
            public function calculating(CartLine $cartLine)
            {
                $cartLine->total = new DataTypesPrice(
                    200,
                    Currency::getDefault()
                );
            }
        });

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
        ]);

        $purchasable = ProductVariant::factory()->create();

        Price::factory()->create([
            'price'          => 100,
            'tier'           => 1,
            'currency_id'    => $currency->id,
            'priceable_type' => get_class($purchasable),
            'priceable_id'   => $purchasable->id,
        ]);

        $cart->lines()->createMany([
            ['quantity' => 1, 'purchasable_type' => ProductVariant::class, 'purchasable_id' => $purchasable->id],
        ]);

        $cartLine = $cart->lines->first();

        $this->assertEquals(0, $cartLine->total?->value);

        app(Pipeline::class)
            ->through(
                $modifiers->getModifiers()->toArray()
            )->send($cartLine)
            ->via('processCalculating')
            ->thenReturn();

        $this->assertEquals(200, $cartLine->total?->value);
    }
}
