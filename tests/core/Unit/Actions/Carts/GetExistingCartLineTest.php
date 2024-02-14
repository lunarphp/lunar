<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Lunar\Actions\Carts\GetExistingCartLine;
use Lunar\Models\Cart;
use Lunar\Models\CartLine;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can get basic cart line', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 100,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasable),
        'priceable_id' => $purchasable->id,
    ]);

    $cartLine = $cart->lines()->create([
        'purchasable_id' => $purchasable->id,
        'purchasable_type' => ProductVariant::class,
        'quantity' => 1,
        'meta' => null,
    ]);

    $action = new GetExistingCartLine;

    $existing = $action->execute($cart, $purchasable);

    expect($existing)->toBeInstanceOf(CartLine::class);
    expect($existing->id)->toEqual($cartLine->id);
});

test('can get cart line with different meta', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 100,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasable),
        'priceable_id' => $purchasable->id,
    ]);

    $cartLineAMeta = [
        'key_a' => 'value_a',
        'key_b' => 'value_b',
    ];

    $cartLineBMeta = [
        'key_a' => [
            'child_a',
            'child_b',
        ],
    ];

    $cartLineCMeta = [
        'key_a' => [
            'parent_a' => [
                'child_a' => 'child_a_value',
                'child_b',
            ],
            'parent_b' => [
                'child_a' => 'child_a_value',
                'child_b',
            ],
        ],
    ];

    $cart->lines()->createMany([
        [
            'purchasable_id' => $purchasable->id,
            'purchasable_type' => ProductVariant::class,
            'quantity' => 1,
            'meta' => $cartLineAMeta,
        ],
        [
            'purchasable_id' => $purchasable->id,
            'purchasable_type' => ProductVariant::class,
            'quantity' => 1,
            'meta' => $cartLineBMeta,
        ],
        [
            'purchasable_id' => $purchasable->id,
            'purchasable_type' => ProductVariant::class,
            'quantity' => 1,
            'meta' => $cartLineCMeta,
        ],
    ]);

    $action = new GetExistingCartLine;

    foreach ($cart->lines as $line) {
        $meta = (array) $line->meta;
        foreach (range(1, 10) as $i) {
            shuffle_assoc($meta);
            $existing = $action->execute($cart, $purchasable, $meta);
            expect($existing)->toBeInstanceOf(CartLine::class);
            expect($line->id)->toEqual($line->id);
        }
    }
});

function shuffle_assoc($list)
{
    if (! is_array($list)) {
        return $list;
    }

    $keys = array_keys($list);
    shuffle($keys);
    $random = [];
    foreach ($keys as $key) {
        $random[$key] = $list[$key];
    }

    return $random;
}
