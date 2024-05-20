<?php

use function Pest\Laravel\{assertDatabaseHas};

uses(\Lunar\Tests\Opayo\TestCase::class);
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('can handle a successful payment', function () {
    $cart = buildCart();

    $response = (new \Lunar\Opayo\OpayoPaymentType())->cart($cart)->withData([
        'merchant_key' => 'SUCCESS',
        'card_identifier' => 'CARDTOKEN',
        'status' => 'payment-received',
    ])->authorize();

    $order = $cart->completedOrder()->first();

    expect($response->success)->toBe(true)
        ->and($response->status)->toEqual(\Lunar\Opayo\Facades\Opayo::AUTH_SUCCESSFUL)
        ->and($order)->toBeInstanceOf(\Lunar\Models\Order::class)
        ->and($order->status)->toBe('payment-received')
        ->and($order->placed_at)->not->toBeNull();

    assertDatabaseHas(\Lunar\Models\Transaction::class, [
        'success' => true,
        'type' => 'capture',
        'driver' => 'opayo',
        'reference' => 'DB79BA2D-05DA-5B85-D188-1293D16BBAC7',
        'status' => 'Ok',
        'card_type' => 'Visa',
        'last_four' => '1111',
    ]);
});

it('can handle a failed payment', function () {
    $cart = buildCart();

    $response = (new \Lunar\Opayo\OpayoPaymentType())->cart($cart)->withData([
        'merchant_key' => 'FAILED',
        'card_identifier' => 'CARDTOKEN',
        'status' => 'payment-received',
    ])->authorize();

    $order = $cart->completedOrder()->first();

    expect($cart->completedOrder()->first())->toBeNull()
        ->and($response->status)->toEqual(\Lunar\Opayo\Facades\Opayo::AUTH_FAILED)
        ->and($cart->draftOrder()->first())
        ->toBeInstanceOf(\Lunar\Models\Order::class);

    assertDatabaseHas(\Lunar\Models\Transaction::class, [
        'success' => false,
        'type' => 'capture',
        'driver' => 'opayo',
        'reference' => 'DB79BA2D-05DA-5B85-D188-1293D16BBAC7',
        'status' => 'NotAuthed',
        'card_type' => 'Visa',
        'last_four' => '1111',
    ]);
});

it('can handle a 3DSv2 response', function () {
    $cart = buildCart();

    $response = (new \Lunar\Opayo\OpayoPaymentType())->cart($cart)->withData([
        'merchant_key' => 'SUCCESS_3DSV2',
        'card_identifier' => 'CARDTOKEN',
        'status' => 'payment-received',
    ])->authorize();

    expect($cart->completedOrder()->first())->toBeNull()
        ->and($response->status)->toEqual(\Lunar\Opayo\Facades\Opayo::THREED_AUTH)
        ->and($cart->draftOrder()->first())
        ->toBeInstanceOf(\Lunar\Models\Order::class);
});

it('can process a failed 3DSv2 response', function () {
    $cart = buildCart();

    $response = (new \Lunar\Opayo\OpayoPaymentType())->cart($cart)->withData([
        'cres' => '3DSV2_FAILURE',
        'pares' => '3DSV2_FAILURE',
        'transaction_id' => '3DSV2_FAILURE',
        'status' => 'payment-received',
    ])->threedsecure();

    $order = $cart->completedOrder()->first();

    expect($cart->completedOrder()->first())->toBeNull()
        ->and($response->status)
        ->toEqual(\Lunar\Opayo\Facades\Opayo::AUTH_FAILED)
        ->and($cart->draftOrder()->first())
        ->toBeInstanceOf(\Lunar\Models\Order::class)
        ->and($cart->draftOrder()->first()->placed_at)
        ->toBeNull();

    assertDatabaseHas(\Lunar\Models\Transaction::class, [
        'success' => false,
        'type' => 'capture',
        'driver' => 'opayo',
        'reference' => 'DB79BA2D-05DA-5B85-D188-1293D16BBAC7',
        'status' => 'NotAuthed',
        'card_type' => 'Visa',
        'last_four' => '1111',
    ]);
});

it('can process a successful 3DSv2 response', function () {
    $cart = buildCart();

    $response = (new \Lunar\Opayo\OpayoPaymentType())->cart($cart)->withData([
        'cres' => '3DSV2_SUCCESS',
        'pares' => '3DSV2_SUCCESS',
        'transaction_id' => '3DSV2_SUCCESS',
        'status' => 'payment-received',
    ])->threedsecure();

    $order = $cart->completedOrder()->first();

    expect($response->success)->toBe(true)
        ->and($response->status)->toEqual(\Lunar\Opayo\Facades\Opayo::AUTH_SUCCESSFUL)
        ->and($order)->toBeInstanceOf(\Lunar\Models\Order::class)
        ->and($order->status)->toBe('payment-received')
        ->and($order->placed_at)->not->toBeNull();

    assertDatabaseHas(\Lunar\Models\Transaction::class, [
        'success' => true,
        'type' => 'capture',
        'driver' => 'opayo',
        'reference' => 'DB79BA2D-05DA-5B85-D188-1293D16BBAC7',
        'status' => 'Ok',
        'card_type' => 'Visa',
        'last_four' => '1111',
    ]);
});
