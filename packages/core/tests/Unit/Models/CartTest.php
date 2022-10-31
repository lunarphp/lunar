<?php

namespace Lunar\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lunar\Managers\CartManager;
use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\Order;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Tests\Stubs\User as StubUser;
use Lunar\Tests\TestCase;

/**
 * @group lunar.carts
 */
class CartTest extends TestCase
{
    use RefreshDatabase;

    private function setAuthUserConfig()
    {
        Config::set('auth.providers.users.model', 'Lunar\Tests\Stubs\User');
    }

    /** @test */
    public function can_make_a_cart()
    {
        $currency = Currency::factory()->create();
        $channel = Channel::factory()->create();

        $cart = Cart::create([
            'currency_id' => $currency->id,
            'channel_id' => $channel->id,
            'meta' => ['foo' => 'bar'],
        ]);

        $this->assertDatabaseHas((new Cart())->getTable(), [
            'currency_id' => $currency->id,
            'channel_id' => $channel->id,
            'meta' => json_encode(['foo' => 'bar']),
        ]);

        $variant = ProductVariant::factory()->create();

        $cart->lines()->create([
            'purchasable_type' => ProductVariant::class,
            'purchasable_id' => $variant->id,
            'quantity' => 1,
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
            'channel_id' => $channel->id,
        ]);

        // dd(CartManager::class);
        $this->assertInstanceOf(CartManager::class, $cart->getManager());
    }

    /** @test */
    public function can_associate_cart_with_user_with_no_customer_attached()
    {
        $this->setAuthUserConfig();

        $currency = Currency::factory()->create();
        $channel = Channel::factory()->create();
        $user = StubUser::factory()->create();

        $cart = Cart::create([
            'currency_id' => $currency->id,
            'channel_id' => $channel->id,
            'user_id' => $user->getKey(),
        ]);

        $this->assertInstanceOf(CartManager::class, $cart->getManager());
    }

    /** @test */
    public function will_not_retrieve_user_cart_if_order_is_present()
    {
        $this->setAuthUserConfig();

        $currency = Currency::factory()->create();
        $channel = Channel::factory()->create();
        $user = StubUser::factory()->create();

        $cart = Cart::create([
            'order_id' => Order::factory()->create()->id,
            'currency_id' => $currency->id,
            'channel_id' => $channel->id,
            'user_id' => $user->getKey(),
        ]);

        $this->assertNull(
            Cart::whereUserId($user->getKey())->active()->first()
        );
    }

    /** @test */
    public function can_retrieve_active_cart()
    {
        $this->setAuthUserConfig();

        $currency = Currency::factory()->create();
        $channel = Channel::factory()->create();
        $user = StubUser::factory()->create();

        $cart = Cart::create([
            'currency_id' => $currency->id,
            'channel_id' => $channel->id,
            'user_id' => $user->getKey(),
        ]);

        $this->assertEquals(
            $cart->id,
            Cart::whereUserId($user->getKey())->active()->first()->id
        );
    }

    /** @test */
    public function can_associate_cart_with_user_with_customer_attached()
    {
        $this->setAuthUserConfig();

        $currency = Currency::factory()->create();
        $channel = Channel::factory()->create();
        $user = StubUser::factory()->create();
        $customer = Customer::factory()->create();

        $customer->users()->attach($user);

        $cart = Cart::create([
            'currency_id' => $currency->id,
            'channel_id' => $channel->id,
            'user_id' => $user->getKey(),
        ]);

        $this->assertInstanceOf(CartManager::class, $cart->getManager());
    }

    /** @test */
    public function can_calculate_the_cart()
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

        $this->actingAs(
            StubUser::factory()->create()
        );

        $cart->calculate();

        $this->assertEquals(100, $cart->subTotal->value);
        $this->assertEquals(120, $cart->total->value);
        $this->assertCount(1, $cart->taxBreakdown);
    }
}
