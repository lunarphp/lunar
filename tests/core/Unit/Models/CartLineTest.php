<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Exceptions\NonPurchasableItemException;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Order;
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

test('a line added for a discount on a placed cart stays as an ordinary line when the discount is deleted', function () {
    $discount = Discount::factory()->create();

    $line = CartLine::factory()->create([
        'added_by_discount_id' => $discount->id,
    ]);

    expect($line->addedByDiscount->is($discount))->toBeTrue()
        ->and($line->isAddedByDiscount())->toBeTrue();

    // A cart with a completed order keeps its lines as history (spec
    // remove-unearned-reward-lines); a cart still being shopped loses them.
    Order::factory()->create(['cart_id' => $line->cart_id, 'placed_at' => now()]);

    $discount->delete();

    $line = $line->fresh();

    expect($line)->not->toBeNull()
        ->and($line->added_by_discount_id)->toBeNull()
        ->and($line->isAddedByDiscount())->toBeFalse();
})->skip(
    fn () => DB::connection()->getDriverName() === 'sqlite',
    'The foreign key nulls the column; the SQLite test connection does not enforce foreign keys.'
);
