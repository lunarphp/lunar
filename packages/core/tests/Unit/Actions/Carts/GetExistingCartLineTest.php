<?php

namespace Lunar\Tests\Unit\Actions\Carts;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Actions\Carts\GetExistingCartLine;
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
class GetExistingCartLineTest extends TestCase
{
    use RefreshDatabase;

    /**
    * @test
    */
    public function can_get_basic_cart_line()
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

        $cartLine = $cart->lines()->create([
           'purchasable_id' =>  $purchasable->id,
           'purchasable_type' => ProductVariant::class,
           'quantity' => 1,
           'meta' => null,
        ]);

        $action = new GetExistingCartLine;

        $existing = $action->execute($cart, $purchasable);

        $this->assertInstanceOf(CartLine::class, $existing);
        $this->assertEquals($cartLine->id, $existing->id);
    }

    /**
    * @test
    */
    public function can_get_cart_line_with_different_meta()
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

        $cartLineAMeta = [
            'key_a' => 'value_a',
            'key_b' => 'value_b',
        ];

        $cartLineBMeta = [
            'key_a' => [
                'child_a',
                'child_b',
            ],
        ];

        $cartLineCMeta = [
            'key_a' => [
                'parent_a' => [
                    'child_a' => 'child_a_value',
                    'child_b',
                ],
                'parent_b' => [
                    'child_a' => 'child_a_value',
                    'child_b',
                ],
            ],
        ];

        $cart->lines()->createMany([
            [
               'purchasable_id' =>  $purchasable->id,
               'purchasable_type' => ProductVariant::class,
               'quantity' => 1,
               'meta' => $cartLineAMeta,
            ],
            [
               'purchasable_id' =>  $purchasable->id,
               'purchasable_type' => ProductVariant::class,
               'quantity' => 1,
               'meta' => $cartLineBMeta,
            ],
            [
                'purchasable_id' =>  $purchasable->id,
                'purchasable_type' => ProductVariant::class,
                'quantity' => 1,
                'meta' => $cartLineCMeta,
            ]
        ]);

        $action = new GetExistingCartLine;

        function shuffle_assoc($list) {
          if (!is_array($list)) return $list;

          $keys = array_keys($list);
          shuffle($keys);
          $random = array();
          foreach ($keys as $key) {
            $random[$key] = $list[$key];
          }
          return $random;
        }

        foreach ($cart->lines as $line) {
            $meta = (array) $line->meta;
            foreach(range(1, 10) as $i) {
                shuffle_assoc($meta);
                $existing = $action->execute($cart, $purchasable, $meta);
                $this->assertInstanceOf(CartLine::class, $existing);
                $this->assertEquals($line->id, $line->id);
            }
        }
    }
}
