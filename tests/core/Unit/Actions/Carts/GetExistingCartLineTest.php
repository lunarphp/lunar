<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Carts\GetExistingCartLine;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

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
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    $cartLine = $cart->lines()->create([
        'purchasable_id' => $purchasable->id,
        'purchasable_type' => $purchasable->getMorphClass(),
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
        'priceable_type' => $purchasable->getMorphClass(),
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
            'purchasable_type' => $purchasable->getMorphClass(),
            'quantity' => 1,
            'meta' => $cartLineAMeta,
        ],
        [
            'purchasable_id' => $purchasable->id,
            'purchasable_type' => $purchasable->getMorphClass(),
            'quantity' => 1,
            'meta' => $cartLineBMeta,
        ],
        [
            'purchasable_id' => $purchasable->id,
            'purchasable_type' => $purchasable->getMorphClass(),
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

test('a product the shopper adds never goes onto a line core added for a discount', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 100,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    $rewardLine = $cart->lines()->create([
        'purchasable_id' => $purchasable->id,
        'purchasable_type' => $purchasable->getMorphClass(),
        'quantity' => 1,
        'added_by_discount_id' => Discount::factory()->create()->id,
    ]);

    expect((new GetExistingCartLine)->execute($cart, $purchasable))->toBeNull();

    // Without recalculating: the line's discount applies to nothing here, so a
    // calculation would remove it (spec remove-unearned-reward-lines).
    $cart->add($purchasable, 2, refresh: false);

    $lines = $cart->lines()->get();

    expect($lines)->toHaveCount(2)
        ->and($lines->firstWhere('id', $rewardLine->id)->quantity)->toBe(1)
        ->and($lines->firstWhere('id', '!=', $rewardLine->id)->quantity)->toBe(2)
        ->and($lines->firstWhere('id', '!=', $rewardLine->id)->added_by_discount_id)->toBeNull();
});
