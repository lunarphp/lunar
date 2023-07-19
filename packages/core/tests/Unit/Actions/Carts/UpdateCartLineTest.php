<?php

namespace Lunar\Tests\Unit\Actions\Carts;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Actions\Carts\UpdateCartLine;
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
class UpdateCartLineTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_update_cart_line()
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

        $cart->add($purchasable, 1, ['foo' => 'bar']);

        $this->assertCount(1, $cart->refresh()->lines);

        $line = $cart->lines->first();

        $action = new UpdateCartLine;

        $this->assertDatabaseHas((new CartLine())->getTable(), [
            'quantity' => 1,
            'id' => $line->id,
        ]);

        $action->execute($line->id, 2);

        $this->assertDatabaseHas((new CartLine)->getTable(), [
            'quantity' => 2,
            'id' => $line->id,
            'meta' => cast_to_json(['foo' => 'bar']),
        ]);

        $action->execute($line->id, 2, ['baz' => 'bar']);

        $this->assertDatabaseHas((new CartLine)->getTable(), [
            'quantity' => 2,
            'id' => $line->id,
            'meta' => cast_to_json(['baz' => 'bar']),
        ]);
    }
}
