<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lunar\Core\DataObjects\PaymentAuthorize;
use Lunar\Core\Facades\Payments;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartAddress;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Order;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('can authorize payment', function () {
    $cart = Cart::factory()->create();

    Config::set('lunar.payments.types.offline', [
        'authorized' => 'in-process',
    ]);

    CartAddress::factory()->create([
        'cart_id' => $cart->id,
        'type' => 'billing',
        'country_id' => Country::factory(),
        'first_name' => 'Santa',
        'line_one' => '123 Elf Road',
        'city' => 'Lapland',
        'postcode' => 'BILL',
    ]);

    CartAddress::factory()->create([
        'cart_id' => $cart->id,
        'type' => 'shipping',
        'country_id' => Country::factory(),
        'first_name' => 'Santa',
        'line_one' => '123 Elf Road',
        'city' => 'Lapland',
        'postcode' => 'SHIPP',
    ]);

    $result = Payments::driver('offline')->cart($cart->refresh())->authorize();

    expect($result)->toBeInstanceOf(PaymentAuthorize::class);
    expect($result->success)->toBeTrue();

    expect($cart->refresh()->completedOrder)->toBeInstanceOf(Order::class);
});

test('can set additional meta', function () {
    $cart = Cart::factory()->create();

    Config::set('lunar.payments.types.offline', [
        'authorized' => 'in-process',
    ]);

    CartAddress::factory()->create([
        'cart_id' => $cart->id,
        'type' => 'billing',
        'country_id' => Country::factory(),
        'first_name' => 'Santa',
        'line_one' => '123 Elf Road',
        'city' => 'Lapland',
        'postcode' => 'BILL',
    ]);

    CartAddress::factory()->create([
        'cart_id' => $cart->id,
        'type' => 'shipping',
        'country_id' => Country::factory(),
        'first_name' => 'Santa',
        'line_one' => '123 Elf Road',
        'city' => 'Lapland',
        'postcode' => 'SHIPP',
    ]);

    Payments::driver('offline')->cart($cart->refresh())->withData([
        'meta' => [
            'foo' => 'bar',
        ],
    ])->authorize();

    $order = $cart->refresh()->completedOrder;

    $meta = (array) $order->meta;

    expect($meta['foo'])->toEqual('bar');
});
