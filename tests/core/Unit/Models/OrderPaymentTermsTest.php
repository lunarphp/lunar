<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Enums\PaymentTerms;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true]);
});

test('an order defaults to checkout terms', function () {
    $order = Order::factory()->create();

    expect($order->refresh()->payment_terms)->toBe(PaymentTerms::Checkout)
        ->and((new Order)->payment_terms)->toBe(PaymentTerms::Checkout);
});

test('an unpaid order on account is awaiting payment by terms', function () {
    $order = Order::factory()->create(['total' => 1000, 'payment_terms' => PaymentTerms::OnAccount]);

    expect($order->isAwaitingPaymentByTerms())->toBeTrue()
        ->and($order->paymentStatusLabel())->toBe(PaymentTerms::OnAccount->label())
        ->and($order->paymentStatusLabel())->toBe('On account');
});

test('an unpaid checkout order is not awaiting payment by terms', function () {
    $order = Order::factory()->create(['total' => 1000]);

    expect($order->isAwaitingPaymentByTerms())->toBeFalse()
        ->and($order->paymentStatusLabel())->toBe($order->payment_status->label());
});

test('an order on account reads as paid once the payment is captured', function () {
    $order = Order::factory()->create(['total' => 1000, 'payment_terms' => PaymentTerms::OnPickup]);

    $order->transactions()->create([
        'type' => 'capture', 'success' => true, 'amount' => 1000,
        'driver' => 'offline', 'reference' => 'C', 'status' => 'settled',
    ]);

    $order->refresh();

    expect($order->isAwaitingPaymentByTerms())->toBeFalse()
        ->and($order->paymentStatusLabel())->toBe($order->payment_status->label())
        ->and($order->payment_terms)->toBe(PaymentTerms::OnPickup);
});
