<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Carts\UpdateCartLine;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('can update cart line', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->inStock(1)->create();

    Price::factory()->create([
        'price' => 100,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    $cart->add($purchasable, 1, ['foo' => 'bar']);

    expect($cart->refresh()->lines)->toHaveCount(1);

    $line = $cart->lines->first();

    $action = new UpdateCartLine;

    $this->assertDatabaseHas((new CartLine)->getTable(), [
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
