<?php

namespace Lunar\Tests\Unit\Actions\Carts;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Actions\Carts\AssociateUser;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
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
}
