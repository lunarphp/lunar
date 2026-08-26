<?php

use Illuminate\Support\Facades\Event;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Models\Currency;
use Lunar\Models\Transaction;
use Lunar\Stripe\Events\OrphanedPaymentIntentDetected;
use Lunar\Stripe\Facades\Stripe;
use Lunar\Stripe\Managers\StripeManager;
use Lunar\Stripe\Models\StripePaymentIntent;
use Lunar\Stripe\StripePaymentType;
use Lunar\Tests\Stripe\Unit\TestCase;
use Lunar\Tests\Stripe\Utils\CartBuilder;
use Lunar\Tests\Stripe\Utils\StripeFake;

use function Pest\Laravel\assertDatabaseHas;

uses(TestCase::class);

it('can capture an order', function () {
    $cart = CartBuilder::build();
    $payment = new StripePaymentType;

    StripeFake::forCart($cart);

    $response = $payment->cart($cart)->withData([
        'payment_intent' => 'PI_CAPTURE',
    ])->authorize();

    expect($response)->toBeInstanceOf(PaymentAuthorize::class)
        ->and($response->success)->toBeTrue()
        ->and($cart->refresh()->completedOrder->placed_at)->not()->toBeNull()
        ->and($cart->paymentIntents->first()->intent_id)->toEqual('PI_CAPTURE');

    assertDatabaseHas((new Transaction)->getTable(), [
        'order_id' => $cart->refresh()->completedOrder->id,
        'type' => 'capture',
    ]);
});

it('wont capture an order with mismatched intent amount', function () {
    $cart = CartBuilder::build();
    $payment = new StripePaymentType;

    StripeFake::forCart($cart, ['amount' => 100]);

    $response = $payment->cart($cart)->withData([
        'payment_intent' => 'PI_CAPTURE',
    ])->authorize();

    expect($response)->toBeInstanceOf(PaymentAuthorize::class)
        ->and($response->success)->toBeFalse()
        ->and($response->message)->toEqual('Payment intent amount does not match order total')
        ->and($cart->refresh()->completedOrder)->toBeNull()
        ->and($cart->refresh()->draftOrder)->toBeNull()
        ->and($cart->paymentIntents)->toBeEmpty();
});

it('wont capture an order with greater intent amount', function () {
    $cart = CartBuilder::build();
    $payment = new StripePaymentType;

    StripeFake::forCart($cart, ['amount' => $cart->calculate()->total->value + 1]);

    $response = $payment->cart($cart)->withData([
        'payment_intent' => 'PI_CAPTURE',
    ])->authorize();

    expect($response)->toBeInstanceOf(PaymentAuthorize::class)
        ->and($response->success)->toBeFalse()
        ->and($cart->refresh()->completedOrder)->toBeNull()
        ->and($cart->refresh()->draftOrder)->toBeNull()
        ->and($cart->paymentIntents)->toBeEmpty();
});

it('wont capture an order with mismatched intent currency', function () {
    $cart = CartBuilder::build();
    $payment = new StripePaymentType;

    StripeFake::forCart($cart, ['currency' => 'xyz']);

    $response = $payment->cart($cart)->withData([
        'payment_intent' => 'PI_CAPTURE',
    ])->authorize();

    expect($response)->toBeInstanceOf(PaymentAuthorize::class)
        ->and($response->success)->toBeFalse()
        ->and($response->message)->toEqual('Payment intent amount does not match order total')
        ->and($cart->refresh()->completedOrder)->toBeNull()
        ->and($cart->refresh()->draftOrder)->toBeNull()
        ->and($cart->paymentIntents)->toBeEmpty();
});

it('will capture an order with mismatched intent amount if allowed', function () {
    $cart = CartBuilder::build();
    $payment = new StripePaymentType;

    StripeFake::forCart($cart, ['amount' => 100]);

    $response = $payment->cart($cart)->withData([
        'payment_intent' => 'PI_CAPTURE',
    ])->allowPartialPayment()->authorize();

    expect($response)->toBeInstanceOf(PaymentAuthorize::class)
        ->and($response->success)->toBeTrue()
        ->and($cart->refresh()->completedOrder)->not()->toBeNull()
        ->and($cart->refresh()->draftOrder)->toBeNull()
        ->and($cart->paymentIntents->first()->intent_id)->toEqual('PI_CAPTURE');

    assertDatabaseHas((new Transaction)->getTable(), [
        'order_id' => $cart->refresh()->completedOrder->id,
        'type' => 'capture',
    ]);
});

it('can handle failed payments', function () {
    $cart = CartBuilder::build();

    StripeFake::forCart($cart);

    $payment = new StripePaymentType;

    $response = $payment->cart($cart)->withData([
        'payment_intent' => 'PI_FAIL',
    ])->authorize();

    expect($response)->toBeInstanceOf(PaymentAuthorize::class)
        ->and($response->success)->toBeFalse()
        ->and($cart->refresh()->completedOrder)->toBeNull()
        ->and($cart->currentDraftOrder())->not()->toBeNull();

    assertDatabaseHas((new Transaction)->getTable(), [
        'order_id' => $cart->currentDraftOrder()->id,
        'type' => 'capture',
        'success' => false,
    ]);
});

it('can retrieve existing payment intent', function () {
    $cart = CartBuilder::build([
        'meta' => [
            'payment_intent' => 'PI_FOOBAR',
        ],
    ]);

    Stripe::createIntent($cart->calculate(), []);

    expect($cart->refresh()->meta['payment_intent'])->toBe('PI_FOOBAR');
});

it('can handle multiple payment events', function () {
    $cart = CartBuilder::build();
    $order = $cart->createOrder();

    StripeFake::forOrder($order);

    $payment = new StripePaymentType;

    $response = $payment->order($order)->withData([
        'payment_intent' => 'PI_FIRST_FAIL_THEN_CAPTURE',
    ])->authorize();

    expect($response)->toBeInstanceOf(PaymentAuthorize::class)
        ->and($response->success)->toBeFalse()
        ->and($cart->refresh()->completedOrder)->toBeNull()
        ->and($cart->currentDraftOrder())->not()->toBeNull()
        ->and($cart->paymentIntents->first()->intent_id)->toEqual('PI_FIRST_FAIL_THEN_CAPTURE');

    $response = $payment->order($order)->withData([
        'payment_intent' => 'PI_FIRST_FAIL_THEN_CAPTURE',
    ])->authorize();

    expect($response)->toBeInstanceOf(PaymentAuthorize::class)
        ->and($response->success)->toBeTrue()
        ->and($cart->refresh()->completedOrder->placed_at)->not()->toBeNull()
        ->and($cart->paymentIntents->count())->toEqual(1)
        ->and($cart->paymentIntents->first()->intent_id)->toEqual('PI_FIRST_FAIL_THEN_CAPTURE');
});

it('will fail if intent is in final status', function () {
    $cart = CartBuilder::build();
    $order = $cart->createOrder();

    StripeFake::forCart($cart);

    $payment = new StripePaymentType;

    $response = $payment->order($order)->withData([
        'payment_intent' => 'PI_CAPTURE',
    ])->authorize();

    expect($response)->toBeInstanceOf(PaymentAuthorize::class)
        ->and($response->success)->toBeTrue()
        ->and($cart->refresh()->completedOrder->placed_at)->not()->toBeNull()
        ->and($cart->paymentIntents->first()->intent_id)->toEqual('PI_CAPTURE');

    $response = $payment->order($order)->withData([
        'payment_intent' => 'PI_CAPTURE',
    ])->authorize();

    expect($response)->toBeInstanceOf(PaymentAuthorize::class)
        ->and($response->success)->toBeFalse()
        ->and($response->message)->toEqual('Payment intent already processed')
        ->and($cart->refresh()->completedOrder->placed_at)->not()->toBeNull()
        ->and($cart->paymentIntents->first()->intent_id)->toEqual('PI_CAPTURE');
});

it('will fail if cart already has an order', function () {
    $cart = CartBuilder::build();
    $order = $cart->createOrder();

    $order->update([
        'placed_at' => now(),
    ]);

    StripeFake::forCart($cart);

    $payment = new StripePaymentType;

    $response = $payment->cart($cart)->withData([
        'payment_intent' => 'PI_CAPTURE',
    ])->authorize();

    expect($response)->toBeInstanceOf(PaymentAuthorize::class)
        ->and($response->success)->toBeFalse()
        ->and($response->message)->toBeIn([
            'Carts can only have one order associated to them.',
            __('lunar::exceptions.carts.order_exists'),
        ]);
});

it('will fail if payment intent status is requires_payment_method', function () {
    $cart = CartBuilder::build();

    StripeFake::forCart($cart);

    $payment = new StripePaymentType;

    $response = $payment->cart($cart)->withData([
        'payment_intent' => 'PI_REQUIRES_PAYMENT_METHOD',
    ])->authorize();

    expect($response)->toBeInstanceOf(PaymentAuthorize::class);
    expect($response->success)->toBeFalse();

    expect($cart->refresh()->completedOrder)->toBeNull();
});

it('create a pending transaction when status is requires_action', function () {
    $cart = CartBuilder::build();

    StripeFake::forCart($cart);

    $payment = new StripePaymentType;

    $response = $payment->cart($cart)->withData([
        'payment_intent' => 'PI_REQUIRES_ACTION',
    ])->authorize();

    expect($response)->toBeInstanceOf(PaymentAuthorize::class);
    expect($response->success)->toBeFalse();

    expect($cart->refresh()->completedOrder)->toBeNull();
});

it('syncs payment intent status when order creation fails on a succeeded stripe intent', function () {
    Event::fake([OrphanedPaymentIntentDetected::class]);

    $cart = CartBuilder::build();

    // Force createOrder to throw a CartException — strip the addresses
    $cart->addresses()->delete();
    $cart->refresh();

    StripeFake::forCart($cart);

    $payment = new StripePaymentType;

    $response = $payment->cart($cart)->withData([
        'payment_intent' => 'PI_CAPTURE',
    ])->authorize();

    expect($response)->toBeInstanceOf(PaymentAuthorize::class)
        ->and($response->success)->toBeFalse();

    // Status must be synced from Stripe so the row is no longer "active"
    assertDatabaseHas(StripePaymentIntent::class, [
        'intent_id' => 'PI_CAPTURE',
        'cart_id' => $cart->id,
        'status' => 'succeeded',
    ]);

    $intentRow = StripePaymentIntent::where('intent_id', 'PI_CAPTURE')->first();
    expect($intentRow->processed_at)->not()->toBeNull();

    Event::assertDispatched(OrphanedPaymentIntentDetected::class, function ($event) use ($cart) {
        return $event->paymentIntentId === 'PI_CAPTURE'
            && $event->cartId === $cart->id;
    });
});

it('syncs payment intent status when order creation fails on a non-succeeded stripe intent', function () {
    Event::fake([OrphanedPaymentIntentDetected::class]);

    $cart = CartBuilder::build();

    $cart->addresses()->delete();
    $cart->refresh();

    StripeFake::forCart($cart);

    $payment = new StripePaymentType;

    $response = $payment->cart($cart)->withData([
        'payment_intent' => 'PI_FAIL',
    ])->authorize();

    expect($response->success)->toBeFalse();

    assertDatabaseHas(StripePaymentIntent::class, [
        'intent_id' => 'PI_FAIL',
        'cart_id' => $cart->id,
        'status' => 'requires_payment_method',
    ]);

    Event::assertNotDispatched(OrphanedPaymentIntentDetected::class);
});

it('can return correct payment checks', function () {
    Currency::factory()->create();

    $cart = buildCart();

    $order = $cart->createOrder();

    $transactionA = Transaction::factory()->create([
        'order_id' => $order->id,
        'driver' => 'stripe',
        'meta' => [
            'address_line1_check' => 'pass',
            'address_postal_code_check' => 'pass',
            'cvc_check' => 'pass',
        ],
    ]);

    $transactionB = Transaction::factory()->create([
        'order_id' => $order->id,
        'driver' => 'stripe',
        'meta' => [
            'address_line1_check' => 'fail',
            'address_postal_code_check' => 'fail',
            'cvc_check' => 'fail',
        ],
    ]);

    $transactionC = Transaction::factory()->create([
        'order_id' => $order->id,
        'driver' => 'stripe',
        'meta' => [
            'address_line1_check' => 'unavailable',
            'address_postal_code_check' => 'unavailable',
            'cvc_check' => 'unavailable',
        ],
    ]);

    $transactionD = Transaction::factory()->create([
        'order_id' => $order->id,
        'driver' => 'stripe',
        'meta' => [
            'address_line1_check' => 'unchecked',
            'address_postal_code_check' => 'unchecked',
            'cvc_check' => 'unchecked',
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
        ->not
        ->toBe(true)
        ->and($paymentBChecks[1]->successful)
        ->not
        ->toBe(true)
        ->and($paymentBChecks[2]->successful)
        ->not
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

it('authorizes a cart whose currency needs rescaling for stripe', function () {
    $cart = CartBuilder::build(currencyParams: [
        'code' => 'HUF',
        'decimal_places' => 0,
    ]);
    $payment = new StripePaymentType;

    $cart->calculate();

    // Stripe holds HUF amounts multiplied by 100, as createIntent sends them.
    StripeFake::forCart($cart, [
        'amount' => StripeManager::toStripeAmount($cart->total->value, $cart->currency),
    ]);

    $response = $payment->cart($cart)->withData([
        'payment_intent' => 'PI_CAPTURE',
    ])->authorize();

    expect($response->success)->toBeTrue()
        ->and($cart->refresh()->completedOrder->placed_at)->not()->toBeNull();

    // The charges fixture reports 1099 Stripe sub-units; stored back as 11 HUF.
    $transaction = $cart->completedOrder->transactions()->where('type', 'capture')->first();

    expect($transaction->amount->value)->toBe(11);
});

it('rejects an intent holding the raw lunar value for a rescaled currency', function () {
    $cart = CartBuilder::build(currencyParams: [
        'code' => 'HUF',
        'decimal_places' => 0,
    ]);
    $payment = new StripePaymentType;

    $cart->calculate();

    // An intent holding the raw stored value — 100x off Stripe's HUF scale.
    StripeFake::forCart($cart, ['amount' => $cart->total->value]);

    $response = $payment->cart($cart)->withData([
        'payment_intent' => 'PI_CAPTURE',
    ])->authorize();

    expect($response->success)->toBeFalse()
        ->and($response->message)->toEqual('Payment intent amount does not match order total');
});

it('converts capture and refund amounts to stripe scale and stores refunds in lunar scale', function () {
    $cart = CartBuilder::build(currencyParams: [
        'code' => 'HUF',
        'decimal_places' => 0,
    ]);
    $payment = new StripePaymentType;

    $cart->calculate();

    $mock = StripeFake::forCart($cart, [
        'amount' => StripeManager::toStripeAmount($cart->total->value, $cart->currency),
    ]);

    $payment->cart($cart)->withData([
        'payment_intent' => 'PI_CAPTURE',
    ])->authorize();

    $order = $cart->refresh()->completedOrder;
    $transaction = $order->transactions()->where('type', 'capture')->first();

    $payment->capture($transaction, 500);

    $captureRequest = collect($mock->requests)->last(
        fn ($r) => $r['method'] == 'post' && str_contains($r['url'], 'capture')
    );

    expect($captureRequest['params']['amount_to_capture'])->toBe(50000);

    $payment->refund($transaction, 500, 'test refund');

    $refundRequest = collect($mock->requests)->last(
        fn ($r) => $r['method'] == 'post' && str_contains($r['url'], 'refunds')
    );

    expect($refundRequest['params']['amount'])->toBe(50000);

    // The refund transaction is stored back in Lunar's scale.
    $refund = $order->transactions()->where('type', 'refund')->first();

    expect($refund->amount->value)->toBe(500);
});
