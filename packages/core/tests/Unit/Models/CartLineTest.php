<?php

uses(\Lunar\Tests\TestCase::class);
use Lunar\Exceptions\NonPurchasableItemException;
use Lunar\Models\Cart;
use Lunar\Models\CartLine;
use Lunar\Models\Channel;
use Lunar\Models\ProductVariant;
use Lunar\Tests\Stubs\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can make a cart line', function () {
    $cart = Cart::factory()->create([
        'user_id' => User::factory(),
    ]);

    $data = [
        'cart_id' => $cart->id,
        'quantity' => 1,
        'purchasable_type' => ProductVariant::class,
        'purchasable_id' => ProductVariant::factory()->create()->id,
    ];

    CartLine::create($data);

    $this->assertDatabaseHas((new CartLine())->getTable(), $data);
});

test('only purchasables can be added to a cart', function () {
    $cart = Cart::factory()->create([
        'user_id' => User::factory(),
    ]);

    $data = [
        'cart_id' => $cart->id,
        'quantity' => 1,
        'purchasable_type' => Channel::class,
        'purchasable_id' => Channel::factory()->create()->id,
    ];

    $this->expectException(NonPurchasableItemException::class);

    CartLine::create($data);

    $this->assertDatabaseMissing((new CartLine())->getTable(), $data);
});
