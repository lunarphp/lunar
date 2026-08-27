<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Lunar\Core\DataObjects\PaymentAuthorize;
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

it('captures an approved order and places the order', function () {
    $cart = CartBuilder::build()->calculate();

    PaypalFake::forCart($cart);

    $response = (new PaypalPaymentType)->cart($cart)->withData([
        'paypal_order_id' => '5O190127TN364715T',
    ])->authorize();

    expect($response)->toBeInstanceOf(PaymentAuthorize::class)
        ->and($response->success)->toBeTrue()
        ->and($response->paymentType)->toEqual('paypal');

    $order = $cart->refresh()->completedOrder;

    expect($order)->not->toBeNull()
        ->and($order->placed_at)->not->toBeNull();

    assertDatabaseHas((new Transaction)->getTable(), [
        'order_id' => $order->id,
        'type' => 'capture',
        'driver' => 'paypal',
        'reference' => '3C679366HH908993F',
    ]);
});

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
    $cart = CartBuilder::build()->calculate();

    PaypalFake::forCart($cart, ['capture_status' => 'DECLINED']);

    $response = (new PaypalPaymentType)->cart($cart)->withData([
        'paypal_order_id' => '5O190127TN364715T',
    ])->authorize();

    expect($response->success)->toBeFalse();
});

it('refuses to authorize an order that is already placed', function () {
    $cart = CartBuilder::build()->calculate();

    PaypalFake::forCart($cart);

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
            'amount' => ['value' => '5.00', 'currency_code' => $order->currency_code],
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

it('scales the captured amount without losing a minor unit', function () {
    $cart = CartBuilder::build()->calculate();

    // 19.99 is the classic case: `(int) (19.99 * 100)` truncates to 1998.
    PaypalFake::forCart($cart, ['amount' => '19.99']);

    (new PaypalPaymentType)->cart($cart)->withData([
        'paypal_order_id' => '5O190127TN364715T',
    ])->authorize();

    expect(Transaction::where('type', 'capture')->first()->amount)->toEqual(1999);
});

it('refuses to authorize when paypal covers less than the total', function () {
    $cart = CartBuilder::build()->calculate();

    PaypalFake::forCart($cart, ['amount' => '0.01']);

    $response = (new PaypalPaymentType)->cart($cart)->withData([
        'paypal_order_id' => '5O190127TN364715T',
    ])->authorize();

    expect($response->success)->toBeFalse()
        ->and($response->message)->toEqual('PayPal order amount does not cover the order total');

    // Refused before the capture call, so no money moved and no order exists.
    Http::assertNotSent(fn ($request) => str_contains($request->url(), '/capture'));

    expect(Transaction::count())->toBe(0)
        ->and($cart->refresh()->completedOrder)->toBeNull();
});

it('places the order when paypal covers more than the total', function () {
    $cart = CartBuilder::build()->calculate();

    PaypalFake::forCart($cart, ['amount' => '9999.00']);

    $response = (new PaypalPaymentType)->cart($cart)->withData([
        'paypal_order_id' => '5O190127TN364715T',
    ])->authorize();

    // Over-payment places the order; the settlement state surfaces the excess.
    expect($response->success)->toBeTrue()
        ->and($cart->refresh()->completedOrder->placed_at)->not->toBeNull();
});

it('refuses to authorize when the paypal currency differs', function () {
    $cart = CartBuilder::build()->calculate();

    PaypalFake::forCart($cart, ['currency' => 'ZZZ']);

    $response = (new PaypalPaymentType)->cart($cart)->withData([
        'paypal_order_id' => '5O190127TN364715T',
    ])->authorize();

    expect($response->success)->toBeFalse()
        ->and($response->message)->toEqual('PayPal order currency does not match the order currency');

    Http::assertNotSent(fn ($request) => str_contains($request->url(), '/capture'));
});

it('allows an under-payment when partial payments are enabled', function () {
    Config::set('lunar.paypal.allow_partial_payment', true);

    $cart = CartBuilder::build()->calculate();

    PaypalFake::forCart($cart, ['amount' => '0.01']);

    expect((new PaypalPaymentType)->cart($cart)->withData([
        'paypal_order_id' => '5O190127TN364715T',
    ])->authorize()->success)->toBeTrue();
});

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
