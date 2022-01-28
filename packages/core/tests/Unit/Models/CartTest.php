<?php

namespace GetCandy\Tests\Unit\Models;

use GetCandy\Managers\CartManager;
use GetCandy\Models\Cart;
use GetCandy\Models\Channel;
use GetCandy\Models\Currency;
use GetCandy\Models\Customer;
use GetCandy\Models\ProductVariant;
use GetCandy\Tests\Stubs\User;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group getcandy.carts
 */
class CartTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_make_a_cart()
    {
        $currency = Currency::factory()->create();
        $channel = Channel::factory()->create();

        $cart = Cart::create([
            'currency_id' => $currency->id,
            'channel_id'  => $channel->id,
            'meta'        => ['foo' => 'bar'],
        ]);

        $this->assertDatabaseHas((new Cart())->getTable(), [
            'currency_id' => $currency->id,
            'channel_id'  => $channel->id,
            'meta'        => json_encode(['foo' => 'bar']),
        ]);

        $variant = ProductVariant::factory()->create();

        $cart->lines()->create([
            'purchasable_type' => ProductVariant::class,
            'purchasable_id'   => $variant->id,
            'quantity'         => 1,
        ]);

        $this->assertCount(1, $cart->lines()->get());
    }

    /** @test */
    public function can_get_cart_manager()
    {
        $currency = Currency::factory()->create();
        $channel = Channel::factory()->create();

        $cart = Cart::create([
            'currency_id' => $currency->id,
            'channel_id'  => $channel->id,
        ]);

        // dd(CartManager::class);
        $this->assertInstanceOf(CartManager::class, $cart->getManager());
    }
    
    /** @test */
    public function can_associate_cart_with_user_with_no_customer_attached()
    {
        $currency = Currency::factory()->create();
        $channel = Channel::factory()->create();
        $user = User::factory()->create();

        $cart = Cart::create([
            'currency_id' => $currency->id,
            'channel_id'  => $channel->id,
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(CartManager::class, $cart->getManager());
    }
    
    /** @test */
    public function can_associate_cart_with_user_with_customer_attached()
    {
        $currency = Currency::factory()->create();
        $channel = Channel::factory()->create();
        $user = User::factory()->create();
        $customer = Customer::factory()->create();
            
        $customer->users()->attach($user);

        $cart = Cart::create([
            'currency_id' => $currency->id,
            'channel_id'  => $channel->id,
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(CartManager::class, $cart->getManager());
    }
}
