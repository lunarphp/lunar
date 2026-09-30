<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Exceptions\NonPurchasableItemException;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\ValueObjects\Cart\TaxBreakdown;
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

test('returns null when the purchasable has been hard-deleted', function () {
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

    expect($line->fresh()->purchasable)->toBeNull();
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

test('refreshing a calculated line reloads it and clears its cached totals', function () {
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

    // What a cart calculation leaves on the line.
    $line->subTotal = new PriceValue(1000, $cart->currency);
    $line->taxBreakdown = new TaxBreakdown;

    CartLine::query()->whereKey($line->id)->update(['quantity' => 3]);

    $line->refresh();

    expect($line->quantity)->toBe(3)
        ->and($line->subTotal)->toBeNull()
        ->and(isset($line->taxBreakdown))->toBeFalse();
});
