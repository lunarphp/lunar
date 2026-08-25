<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Facades\CartSession;
use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

/**
 * CartSession::setCurrency() writes currency_id to the cart row but leaves the
 * already-loaded relations on the in-memory cart untouched. Since prices are
 * resolved from those relations, a cart calculated after the switch is still
 * priced in the old currency.
 */
function currencySwitchCart(): array
{
    CustomerGroup::factory()->create(['default' => true]);
    $channel = Channel::factory()->create(['default' => true]);

    $gbp = Currency::factory()->create(['code' => 'GBP', 'default' => true, 'exchange_rate' => 1]);
    $usd = Currency::factory()->create(['code' => 'USD', 'default' => false, 'exchange_rate' => 1]);

    $variant = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $gbp->id,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
    ]);

    Price::factory()->create([
        'price' => 2500, // $25 — deliberately unrelated to the GBP price
        'min_quantity' => 1,
        'currency_id' => $usd->id,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
    ]);

    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $gbp->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
        'quantity' => 1,
    ]);

    CartSession::use($cart->refresh());

    return [$cart, $gbp, $usd];
}

test('switching the session currency reprices the cart', function () {
    [$cart, $gbp, $usd] = currencySwitchCart();

    $cart->calculate();
    expect($cart->subTotal->value)->toEqual(1000);

    CartSession::setCurrency($usd);

    $cart->calculate();

    expect($cart->subTotal->value)->toEqual(2500);
});

test('switching the session currency reprices a cart read back from the session', function () {
    [$cart, $gbp, $usd] = currencySwitchCart();

    $cart->calculate();

    CartSession::setCurrency($usd);

    expect(CartSession::current()->subTotal->value)->toEqual(2500);
});
