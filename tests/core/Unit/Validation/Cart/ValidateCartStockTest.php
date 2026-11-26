<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Exceptions\Carts\CartException;
use Lunar\Core\Exceptions\Carts\InsufficientStockException;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Validation\Cart\ValidateCartStock;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

function cartLineFor(Cart $cart, ProductVariant $variant, int $quantity, array $meta = []): CartLine
{
    return CartLine::factory()->create([
        'cart_id' => $cart->id,
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
        'quantity' => $quantity,
        'meta' => $meta ?: null,
    ]);
}

function validateCartStock(Cart $cart): bool
{
    return app(ValidateCartStock::class)->using(cart: $cart->refresh())->validate();
}

test('passes when every stock-bound line has the stock it needs', function () {
    $cart = Cart::factory()->create();
    cartLineFor($cart, ProductVariant::factory()->stockBound()->inStock(3)->create(), 3);
    cartLineFor($cart, ProductVariant::factory()->create(), 500);

    expect(validateCartStock($cart))->toBeTrue();
});

test('sums lines of the same variant before checking its stock', function () {
    $cart = Cart::factory()->create();
    $variant = ProductVariant::factory()->stockBound()->inStock(5)->create();

    // Each line fits on its own; together they need 6.
    cartLineFor($cart, $variant, 3, ['engraving' => 'A']);
    cartLineFor($cart, $variant, 3, ['engraving' => 'B']);

    expect(fn () => validateCartStock($cart))->toThrow(InsufficientStockException::class);
});

test('reads stock that sold out after the line was added', function () {
    $cart = Cart::factory()->create();
    $variant = ProductVariant::factory()->stockBound()->inStock(2)->create();
    cartLineFor($cart, $variant, 2);

    $cart->load('lines.purchasable');

    ProductVariant::query()->whereKey($variant->id)->update(['stock_available' => 1]);

    expect(fn () => app(ValidateCartStock::class)->using(cart: $cart)->validate())
        ->toThrow(InsufficientStockException::class);
});

test('counts incoming stock for a variant selling against it', function () {
    $cart = Cart::factory()->create();
    $variant = ProductVariant::factory()->sellsAgainstIncoming()->inStock(1)->create();
    $variant->forceFill(['stock_incoming' => 2])->save();
    cartLineFor($cart, $variant, 3);

    expect(validateCartStock($cart))->toBeTrue();

    cartLineFor($cart, $variant, 1, ['gift' => true]);

    expect(fn () => validateCartStock($cart))->toThrow(InsufficientStockException::class);
});

test('names only the short lines on the exception', function () {
    $cart = Cart::factory()->create();
    $short = ProductVariant::factory()->stockBound()->inStock(1)->create(['sku' => 'SHORT-1']);
    $fine = ProductVariant::factory()->stockBound()->inStock(9)->create(['sku' => 'FINE-1']);
    $shortLine = cartLineFor($cart, $short, 2);
    cartLineFor($cart, $fine, 2);

    try {
        validateCartStock($cart);
        $this->fail('Expected an InsufficientStockException.');
    } catch (InsufficientStockException $e) {
        expect($e)->toBeInstanceOf(CartException::class)
            ->and($e->lines()->pluck('id')->all())->toBe([$shortLine->id])
            ->and($e->getMessage())->toBe(__('lunar::exceptions.carts.insufficient_stock', ['identifier' => 'SHORT-1']));
    }
});

test('order creation stops before writing an order when stock has run out', function () {
    $cart = buildCart();
    $cart->lines->first()->purchasable->update(['continue_selling' => false]);

    expect($cart->refresh()->canCreateOrder())->toBeFalse()
        ->and(fn () => $cart->createOrder())->toThrow(InsufficientStockException::class);

    expect(Order::query()->where('cart_id', $cart->id)->exists())->toBeFalse();
});
