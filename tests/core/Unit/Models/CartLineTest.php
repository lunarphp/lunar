<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Exceptions\NonPurchasableItemException;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\Stubs\User;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class)->group('cross-db');

uses(RefreshDatabase::class);

test('can make a cart line', function () {
    $cart = Cart::factory()->create([
        'user_id' => User::factory(),
    ]);

    $variant = ProductVariant::factory()->create();

    $data = [
        'cart_id' => $cart->id,
        'quantity' => 1,
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
    ];

    CartLine::create($data);

    $this->assertDatabaseHas((new CartLine)->getTable(), $data);
});

test('resolves a soft-deleted purchasable through the relation', function () {
    $cart = Cart::factory()->create([
        'user_id' => User::factory(),
    ]);

    $variant = ProductVariant::factory()->create();

    $line = CartLine::create([
        'cart_id' => $cart->id,
        'quantity' => 1,
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
    ]);

    $variant->delete();

    $line = $line->fresh();

    expect($line->purchasable)->not->toBeNull();
    expect($line->purchasable->id)->toBe($variant->id);
    expect($line->purchasable->trashed())->toBeTrue();
});

test('only purchasables can be added to a cart', function () {
    $cart = Cart::factory()->create([
        'user_id' => User::factory(),
    ]);

    $channel = Channel::factory()->create();

    $data = [
        'cart_id' => $cart->id,
        'quantity' => 1,
        'purchasable_type' => $channel->getMorphClass(),
        'purchasable_id' => $channel->id,
    ];

    $this->expectException(NonPurchasableItemException::class);

    CartLine::create($data);

    $this->assertDatabaseMissing((new CartLine)->getTable(), $data);
});
