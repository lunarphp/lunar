<?php

namespace Lunar\Tests\Unit\Actions\Carts;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Actions\Carts\AssociateUser;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\Order;
use Lunar\Tests\Stubs\User;
use Lunar\Tests\TestCase;

/**
 * @group lunar.actions
 * @group lunar.actions.carts.now
 */
class AssociateUserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function can_associate_a_user()
    {
        $currency = Currency::factory()->create();

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
        ]);

        $this->assertDatabaseHas((new Cart)->getTable(), [
            'user_id' => null,
            'id' => $cart->id,
            'merged_id' => null,
        ]);

        $action = new AssociateUser;

        $user = User::factory()->create();
        $action->execute($cart, $user);

        $this->assertDatabaseHas((new Cart)->getTable(), [
            'user_id' => $user->id,
            'id' => $cart->id,
            'merged_id' => null,
        ]);
    }

    /**
     * @test
     */
    public function cant_associate_user_to_cart_with_order()
    {
        $currency = Currency::factory()->create();

        $user = User::factory()->create();

        $userCart = Cart::factory()->create([
            'user_id' => $user->id,
            'currency_id' => $currency->id,
        ]);

        Order::factory()->create([
            'placed_at' => now(),
            'cart_id' => $userCart->id,
        ]);

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
        ]);

        $this->assertDatabaseHas((new Cart)->getTable(), [
            'user_id' => null,
            'id' => $cart->id,
            'merged_id' => null,
        ]);

        $this->assertDatabaseHas((new Cart)->getTable(), [
            'user_id' => $user->id,
            'id' => $userCart->id,
            'merged_id' => null,
        ]);

        $action = new AssociateUser;

        $action->execute($cart, $user);

        $this->assertDatabaseHas((new Cart)->getTable(), [
            'user_id' => $user->id,
            'id' => $cart->id,
            'merged_id' => null,
        ]);
    }
}
