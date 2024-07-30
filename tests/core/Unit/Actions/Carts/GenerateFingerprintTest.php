<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Lunar\Actions\Carts\GenerateFingerprint;
use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can generate cart fingerprint', function () {
    $currency = Currency::factory()->create();
    $channel = Channel::factory()->create();

    $cart = Cart::create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'meta' => [
            'A' => 'B',
            'C' => 'D',
        ],
    ]);

    $variant = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 100,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($variant),
        'priceable_id' => $variant->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => ProductVariant::class,
        'purchasable_id' => $variant->id,
        'quantity' => 1,
    ]);

    $cart->coupon_code = 'valid-coupon';

    $fingerprint = (new GenerateFingerprint)->execute($cart);
    $fingerprintFromCart = $cart->fingerprint();

    expect($fingerprintFromCart)->toBe($fingerprint);

    $cart->update([
        'meta' => [
            'C' => 'D',
            'A' => 'B',
        ],
    ]);

    expect($cart->fingerprint())->toBe($fingerprintFromCart);

    $cart->update([
        'coupon_code' => null,
    ]);

    $this->assertNotSame($fingerprintFromCart, $cart->fingerprint());

    $cart->update([
        'meta' => null,
    ]);

    $this->assertNotSame($fingerprintFromCart, $cart->fingerprint());

    $line = $cart->lines->first();

    $line->update([
        'quantity' => 999,
    ]);

    $this->assertNotSame($fingerprintFromCart, $cart->fingerprint());
});
