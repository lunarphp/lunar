<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Http;
use Lunar\Core\Models\Transaction;
use Lunar\Paypal\PaypalPaymentType;
use Lunar\Tests\Paypal\Unit\TestCase;
use Lunar\Tests\Paypal\Utils\CartBuilder;
use Lunar\Tests\Paypal\Utils\PaypalFake;

use function Pest\Laravel\assertDatabaseHas;

uses(TestCase::class);

/**
 * The capture endpoint pattern has to be registered before the order-fetch
 * pattern — `Http::fake()` returns the first matching stub, and
 * `*\/v2/checkout/orders/*` would otherwise swallow the capture call too.
 */
function fakeApprovedOrder(string $captureFixture = 'order_captured'): void
{
    PaypalFake::fake([
        '*/v2/checkout/orders/*/capture' => $captureFixture,
        '*/v2/checkout/orders/*' => 'order_approved',
    ]);
}

it('captures the money at paypal but cannot place the order', function () {
    fakeApprovedOrder();

    $cart = CartBuilder::build()->calculate();

    // authorize() writes `status`, a column v2 dropped in favour of the derived
    // payment_status/fulfilment_status pair. The capture has already happened at
    // PayPal by this point, so the customer is charged and no order is placed.
    expect(fn () => (new PaypalPaymentType)->cart($cart)->withData([
        'paypal_order_id' => '5O190127TN364715T',
    ])->authorize())->toThrow(QueryException::class, 'no such column: status');

    // The capture transaction was written before the crash.
    assertDatabaseHas((new Transaction)->getTable(), [
        'type' => 'capture',
        'driver' => 'paypal',
        'reference' => '3C679366HH908993F',
    ]);

    expect($cart->refresh()->completedOrder?->placed_at)->toBeNull();
});
// Slice 2 drops the status write — this becomes a successful authorize.

it('fails when the paypal order does not exist', function () {
    PaypalFake::fake([
        '*/v2/checkout/orders/*' => ['order_not_found', 404],
    ]);

    $cart = CartBuilder::build()->calculate();

    $response = (new PaypalPaymentType)->cart($cart)->withData([
        'paypal_order_id' => 'DOES_NOT_EXIST',
    ])->authorize();

    expect($response->success)->toBeFalse();

    expect(Transaction::count())->toBe(0);
});

it('fails when the capture is declined', function () {
    fakeApprovedOrder('order_capture_declined');

    $cart = CartBuilder::build()->calculate();

    $response = (new PaypalPaymentType)->cart($cart)->withData([
        'paypal_order_id' => '5O190127TN364715T',
    ])->authorize();

    expect($response->success)->toBeFalse();
});

it('refuses to authorize an order that is already placed', function () {
    fakeApprovedOrder();

    $cart = CartBuilder::build()->calculate();
    $order = $cart->createOrder();
    $order->update(['placed_at' => now()]);

    $response = (new PaypalPaymentType)->order($order->refresh())->withData([
        'paypal_order_id' => '5O190127TN364715T',
    ])->authorize();

    expect($response->success)->toBeFalse()
        ->and($response->message)->toEqual('This order has already been placed');
});

it('records a refund transaction against the order', function () {
    PaypalFake::fake([
        '*/v2/payments/captures/*/refund' => 'refund',
    ]);

    // Built directly rather than via authorize(), which cannot currently place an
    // order — see the status-column defect above.
    $order = CartBuilder::build()->calculate()->createOrder();

    $capture = $order->transactions()->create([
        'success' => true,
        'type' => 'capture',
        'driver' => 'paypal',
        'amount' => 1999,
        'reference' => '3C679366HH908993F',
        'status' => 'COMPLETED',
        'card_type' => 'paypal',
        'captured_at' => now(),
    ]);

    $response = (new PaypalPaymentType)->refund($capture, 500, 'Damaged in transit');

    // PaymentRefund carries only success/message on 2.x — the $transaction
    // property arrives with the line-item refunds work (spec 0028).
    expect($response->success)->toBeTrue();

    $refund = $order->transactions()->where('type', 'refund')->first();

    expect($refund)->not->toBeNull()
        ->and($refund->amount)->toEqual(500)
        ->and($refund->reference)->toEqual('1JU08902781691411')
        ->and($refund->notes)->toEqual('Damaged in transit');

    expect(PaypalFake::sentBody('*/v2/payments/captures/*/refund'))
        ->toEqual([
            // 500 minor units sent as "5" — the hardcoded /100 ignores
            // Currency::decimal_places. Slice 2 fixes the scaling.
            'amount' => ['value' => '5', 'currency_code' => $order->currency_code],
        ]);
});

/*
|--------------------------------------------------------------------------
| Known defects
|--------------------------------------------------------------------------
|
| These pin the behaviour spec 0071 sets out to fix, so the slices that fix
| them show the change as an assertion flip rather than a silent difference.
|
*/

it('scales the captured amount through a float, losing a minor unit', function () {
    fakeApprovedOrder();

    $cart = CartBuilder::build()->calculate();

    try {
        (new PaypalPaymentType)->cart($cart)->withData([
            'paypal_order_id' => '5O190127TN364715T',
        ])->authorize();
    } catch (QueryException) {
        // The status-column crash lands after the transaction is written.
    }

    // The fixture captures "19.99". `(int) (19.99 * 100)` truncates to 1998.
    expect(Transaction::where('type', 'capture')->first()->amount)->toEqual(1998);
});
// Slice 2 rescales through Currency::decimal_places — expected value becomes 1999.

it('places the order even when paypal captured less than the total', function () {
    fakeApprovedOrder();

    // The fixture captures 19.99; make the cart cost far more than that.
    $cart = CartBuilder::build(unitPrice: 500.00)->calculate();

    expect($cart->total->decimal())->toBeGreaterThan(19.99);

    // Nothing compares the captured amount against the cart total, so the driver
    // captures 19.99 against a far larger cart and proceeds to place the order
    // (reaching the status-column crash, which is a separate defect).
    expect(fn () => (new PaypalPaymentType)->cart($cart)->withData([
        'paypal_order_id' => '5O190127TN364715T',
    ])->authorize())->toThrow(QueryException::class);

    expect(Transaction::where('type', 'capture')->count())->toBe(1);
});
// Slice 2 adds the amount guard — an under-capture must fail before capturing.

it('reports a successful capture without contacting paypal', function () {
    Http::fake();

    $order = CartBuilder::build()->calculate()->createOrder();

    $capture = $order->transactions()->create([
        'success' => true,
        'type' => 'capture',
        'driver' => 'paypal',
        'amount' => 1999,
        'reference' => '3C679366HH908993F',
        'status' => 'COMPLETED',
        'card_type' => 'paypal',
        'captured_at' => now(),
    ]);

    expect((new PaypalPaymentType)->capture($capture, 100)->success)->toBeTrue();

    Http::assertNothingSent();
});
// Slice 4 makes capture() call the authorization capture endpoint.
