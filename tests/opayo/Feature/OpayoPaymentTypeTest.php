<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Currency;
use Lunar\Models\Order;
use Lunar\Models\Transaction;
use Lunar\Opayo\Facades\Opayo;
use Lunar\Opayo\OpayoPaymentType;
use Lunar\Tests\Opayo\TestCase;

use function Pest\Laravel\assertDatabaseHas;

uses(TestCase::class);
uses(RefreshDatabase::class);

it('can handle a successful payment', function () {
    $cart = buildCart();

    $response = (new OpayoPaymentType)->cart($cart)->withData([
        'merchant_key' => 'SUCCESS',
        'card_identifier' => 'CARDTOKEN',
        'status' => 'payment-received',
    ])->authorize();

    $order = $cart->completedOrder()->first();

    expect($response->success)->toBe(true)
        ->and($response->status)->toEqual(Opayo::AUTH_SUCCESSFUL)
        ->and($order)->toBeInstanceOf(Order::class)
        ->and($order->status)->toBe('payment-received')
        ->and($order->placed_at)->not->toBeNull();

    assertDatabaseHas(Transaction::class, [
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

    $response = (new OpayoPaymentType)->cart($cart)->withData([
        'merchant_key' => 'FAILED',
        'card_identifier' => 'CARDTOKEN',
        'status' => 'payment-received',
    ])->authorize();

    $order = $cart->completedOrder()->first();

    expect($cart->completedOrder()->first())->toBeNull()
        ->and($response->status)->toEqual(Opayo::AUTH_FAILED)
        ->and($cart->currentDraftOrder())
        ->toBeInstanceOf(Order::class);

    assertDatabaseHas(Transaction::class, [
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

    $response = (new OpayoPaymentType)->cart($cart)->withData([
        'merchant_key' => 'SUCCESS_3DSV2',
        'card_identifier' => 'CARDTOKEN',
        'status' => 'payment-received',
    ])->authorize();

    expect($cart->completedOrder()->first())->toBeNull()
        ->and($response->status)->toEqual(Opayo::THREED_AUTH)
        ->and($cart->currentDraftOrder())
        ->toBeInstanceOf(Order::class);
});

it('can process a failed 3DSv2 response', function () {
    $cart = buildCart();

    $response = (new OpayoPaymentType)->cart($cart)->withData([
        'cres' => '3DSV2_FAILURE',
        'pares' => '3DSV2_FAILURE',
        'transaction_id' => '3DSV2_FAILURE',
        'status' => 'payment-received',
    ])->threedsecure();

    $order = $cart->completedOrder()->first();

    expect($cart->completedOrder()->first())->toBeNull()
        ->and($response->status)
        ->toEqual(Opayo::AUTH_FAILED)
        ->and($cart->currentDraftOrder())
        ->toBeInstanceOf(Order::class)
        ->and($cart->currentDraftOrder()->first()->placed_at)
        ->toBeNull();

    assertDatabaseHas(Transaction::class, [
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

    $response = (new OpayoPaymentType)->cart($cart)->withData([
        'cres' => '3DSV2_SUCCESS',
        'pares' => '3DSV2_SUCCESS',
        'transaction_id' => '3DSV2_SUCCESS',
        'status' => 'payment-received',
    ])->threedsecure();

    $order = $cart->completedOrder()->first();

    expect($response->success)->toBe(true)
        ->and($response->status)->toEqual(Opayo::AUTH_SUCCESSFUL)
        ->and($order)->toBeInstanceOf(Order::class)
        ->and($order->status)->toBe('payment-received')
        ->and($order->placed_at)->not->toBeNull();

    assertDatabaseHas(Transaction::class, [
        'success' => true,
        'type' => 'capture',
        'driver' => 'opayo',
        'reference' => 'DB79BA2D-05DA-5B85-D188-1293D16BBAC7',
        'status' => 'Ok',
        'card_type' => 'Visa',
        'last_four' => '1111',
    ]);
});

it('can return correct payment checks', function () {
    Currency::factory()->create();

    $cart = buildCart();

    $order = $cart->createOrder();

    $transactionA = Transaction::factory()->create([
        'order_id' => $order->id,
        'driver' => 'opayo',
        'meta' => [
            'threedSecure' => [
                'address' => 'Matched',
                'postalCode' => 'Matched',
                'securityCode' => 'Matched',
            ],
        ],
    ]);

    $transactionB = Transaction::factory()->create([
        'order_id' => $order->id,
        'driver' => 'opayo',
        'meta' => [
            'threedSecure' => [
                'address' => true,
                'postalCode' => true,
                'securityCode' => true,
            ],
        ],
    ]);

    $transactionC = Transaction::factory()->create([
        'order_id' => $order->id,
        'driver' => 'opayo',
        'meta' => [
            'threedSecure' => [
                'address' => 'NotMatched',
                'postalCode' => 'NotMatched',
                'securityCode' => 'NotMatched',
            ],
        ],
    ]);

    $transactionD = Transaction::factory()->create([
        'order_id' => $order->id,
        'driver' => 'opayo',
        'meta' => [
            'threedSecure' => [
                'address' => false,
                'postalCode' => false,
                'securityCode' => false,
            ],
        ],
    ]);

    $paymentAChecks = $transactionA->paymentChecks();

    expect($paymentAChecks)->toHaveCount(3)
        ->and($paymentAChecks[0]->successful)
        ->toBe(true)
        ->and($paymentAChecks[1]->successful)
        ->toBe(true)
        ->and($paymentAChecks[2]->successful)
        ->toBe(true);

    $paymentBChecks = $transactionB->paymentChecks();

    expect($paymentBChecks)->toHaveCount(3)
        ->and($paymentBChecks[0]->successful)
        ->toBe(true)
        ->and($paymentBChecks[1]->successful)
        ->toBe(true)
        ->and($paymentBChecks[2]->successful)
        ->toBe(true);

    $paymentCChecks = $transactionC->paymentChecks();

    expect($paymentCChecks)->toHaveCount(3)
        ->and($paymentCChecks[0]->successful)
        ->not
        ->toBe(true)
        ->and($paymentCChecks[1]->successful)
        ->not
        ->toBe(true)
        ->and($paymentCChecks[2]->successful)
        ->not
        ->toBe(true);

    $paymentDChecks = $transactionD->paymentChecks();

    expect($paymentDChecks)->toHaveCount(3)
        ->and($paymentCChecks[0]->successful)
        ->not
        ->toBe(true)
        ->and($paymentDChecks[1]->successful)
        ->not
        ->toBe(true)
        ->and($paymentDChecks[2]->successful)
        ->not
        ->toBe(true);
});
