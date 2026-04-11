<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Lunar\Events\OrderPaid;
use Lunar\Models\Transaction;
use Lunar\Paypal\Facades\Paypal;
use Lunar\Paypal\PaypalInterface;
use Lunar\Paypal\PaypalPaymentType;
use Lunar\Tests\Paypal\TestCase;

use function Pest\Laravel\assertDatabaseHas;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Paypal::swap(new class implements PaypalInterface
    {
        public function getOrder(string $orderId): array
        {
            return [
                'status' => 'APPROVED',
                'purchase_units' => [],
            ];
        }

        public function capture(string $orderId): array
        {
            return [
                'status' => 'COMPLETED',
                'purchase_units' => [
                    [
                        'payments' => [
                            'captures' => [
                                [
                                    'status' => 'COMPLETED',
                                    'amount' => ['value' => '6.98'],
                                    'id' => 'CAPTURE-123',
                                    'create_time' => '2024-01-01T00:00:00Z',
                                ],
                            ],
                        ],
                    ],
                ],
            ];
        }

        public function refund($transactionId, string $amount, string $currencyCode): array
        {
            return [
                'id' => 'REFUND-123',
                'status' => 'COMPLETED',
            ];
        }
    });
});

it('dispatches order paid when paypal authorization places the order', function () {
    $cart = buildCart();

    Event::fake([
        OrderPaid::class,
    ]);

    $response = (new PaypalPaymentType)->cart($cart)->withData([
        'paypal_order_id' => 'ORDER-123',
        'status' => 'payment-received',
    ])->authorize();

    $order = $cart->refresh()->completedOrder;

    expect($response->success)->toBeTrue()
        ->and($order)->not->toBeNull()
        ->and($order->status)->toBe('payment-received')
        ->and($order->placed_at)->not->toBeNull();

    assertDatabaseHas((new Transaction)->getTable(), [
        'order_id' => $order->id,
        'type' => 'capture',
        'driver' => 'paypal',
        'reference' => 'CAPTURE-123',
        'status' => 'COMPLETED',
    ]);

    Event::assertDispatched(OrderPaid::class, function (OrderPaid $event) use ($order) {
        return $event->order->is($order)
            && $event->paymentAuthorize->success
            && $event->transaction?->reference === 'CAPTURE-123';
    });
});

it('returns the expanded refund dto for paypal refunds', function () {
    $cart = buildCart();

    (new PaypalPaymentType)->cart($cart)->withData([
        'paypal_order_id' => 'ORDER-123',
        'status' => 'payment-received',
    ])->authorize();

    $order = $cart->refresh()->completedOrder;
    $capture = $order->transactions()->where('type', 'capture')->firstOrFail();

    $response = (new PaypalPaymentType)->order($order)->refund(
        $capture,
        500,
        'Return approved',
    );

    expect($response->success)->toBeTrue()
        ->and($response->refundTransactionId)->not->toBeNull()
        ->and($response->reference)->toBe('REFUND-123')
        ->and($response->status)->toBe('COMPLETED')
        ->and($response->meta)->toBeNull();

    assertDatabaseHas((new Transaction)->getTable(), [
        'id' => $response->refundTransactionId,
        'order_id' => $order->id,
        'type' => 'refund',
        'driver' => 'paypal',
        'amount' => 500,
        'reference' => 'REFUND-123',
        'status' => 'COMPLETED',
        'notes' => 'Return approved',
    ]);
});
