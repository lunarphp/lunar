<?php

namespace GetCandy\Tests\Unit\Models;

use GetCandy\Managers\CartManager;
use GetCandy\Models\Cart;
use GetCandy\Models\Channel;
use GetCandy\Models\Currency;
use GetCandy\Models\Price;
use GetCandy\Models\ProductVariant;
use GetCandy\Models\SavedCart;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group getcandy.carts
 */
class SavedCartTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_make_a_saved_cart()
    {
        $currency = Currency::factory()->create();
        $channel = Channel::factory()->create();

        $cart = Cart::create([
            'currency_id' => $currency->id,
            'channel_id' => $channel->id,
            'meta' => ['foo' => 'bar'],
        ]);

        SavedCart::create([
            'name' => 'Foo',
            'cart_id' => $cart->id,
        ]);

        $this->assertDatabaseHas((new SavedCart)->getTable(), [
            'name' => 'Foo',
            'cart_id' => $cart->id,
        ]);
    }

    /** @test */
    public function can_retrieve_saved_cart_relationship()
    {
        $currency = Currency::factory()->create();
        $channel = Channel::factory()->create();

        $cart = Cart::create([
            'currency_id' => $currency->id,
            'channel_id' => $channel->id,
            'meta' => ['foo' => 'bar'],
        ]);

        $savedCart = SavedCart::create([
            'name' => 'Foo',
            'cart_id' => $cart->id,
        ]);

        $this->assertInstanceOf(Cart::class, $savedCart->cart);
        $this->assertEquals($cart->id, $savedCart->id);
    }
}
