<?php

uses(\Lunar\Tests\Core\TestCase::class);
use Lunar\Actions\Carts\UpdateCartLine;
use Lunar\Models\Cart;
use Lunar\Models\CartLine;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can update cart line', function () {
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

    expect($cart->refresh()->lines)->toHaveCount(1);

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
        'meta' => json_encode(['foo' => 'bar']),
    ]);

    $action->execute($line->id, 2, ['baz' => 'bar']);

    $this->assertDatabaseHas((new CartLine)->getTable(), [
        'quantity' => 2,
        'id' => $line->id,
        'meta' => json_encode(['baz' => 'bar']),
    ]);
});
