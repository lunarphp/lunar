<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use Lunar\Checkout\Contracts\PaymentMethodRegistry;
use Lunar\Checkout\Exceptions\PaymentRecordingException;
use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Checkout\PaymentMethods\AbstractPaymentMethod;
use Lunar\Checkout\States\CheckoutSession\Completed;
use Lunar\Checkout\States\CheckoutSession\PaymentProcessing;
use Lunar\Core\Contracts\CreatesPaymentIntents;
use Lunar\Core\Contracts\SupportsPaymentIntents;
use Lunar\Core\DataObjects\PaymentAuthorize;
use Lunar\Core\DataObjects\PaymentIntentDescriptor;
use Lunar\Core\Enums\PaymentIntentStatus;
use Lunar\Core\Facades\CartSession;
use Lunar\Core\Facades\Payments;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Order;
use Lunar\Core\PaymentTypes\OfflinePayment;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;

uses(TestCase::class, RefreshDatabase::class);

/**
 * A gateway whose authorize() behaves like a real one: it records the money
 * as a Transaction row on the order it was handed, the way Stripe's
 * UpdateOrderFromIntent does. Calls are recorded so the tests can assert the
 * checkout driver ran the shared authorize() path itself (spec 0002 §E)
 * instead of leaving it to a webhook.
 */
class RecordingGateway extends OfflinePayment implements CreatesPaymentIntents, SupportsPaymentIntents
{
    /** @var array<int, array{order_id: int, data: array<string, mixed>}> */
    public static array $authorizeCalls = [];

    public static bool $throwOnAuthorize = false;

    public function createIntent(Cart $cart): PaymentIntentDescriptor
    {
        return new PaymentIntentDescriptor('pi_recording_'.$cart->id);
    }

    public function fetchIntent(string $reference): PaymentIntentStatus
    {
        return PaymentIntentStatus::Captured;
    }

    public function voidIntent(string $reference): void {}

    public function refundIntent(string $reference, int $amountMinor, string $idempotencyKey): string
    {
        return 'refund_'.$reference;
    }

    public function authorize(): ?PaymentAuthorize
    {
        // Cart-first (the webhook path) creates the order here, as OfflinePayment does.
        if (! $this->order) {
            $this->order = $this->cart->draftOrder()->first() ?: $this->cart->createOrder();
        }

        static::$authorizeCalls[] = ['order_id' => $this->order->id, 'data' => $this->data];

        if (static::$throwOnAuthorize) {
            throw new RuntimeException('gateway unreachable');
        }

        $this->order->transactions()->create([
            'success' => true,
            'type' => 'capture',
            'driver' => 'recording',
            'amount' => (int) $this->order->total,
            'reference' => $this->data['payment_intent'],
            'status' => 'succeeded',
        ]);

        return parent::authorize();
    }
}

class RecordingMethod extends AbstractPaymentMethod
{
    public function handle(): string
    {
        return 'recording';
    }

    public function label(): string
    {
        return 'Card';
    }

    public function driver(): string
    {
        return 'recording';
    }

    public function requiresIntent(): bool
    {
        return true;
    }

    public function component(): string
    {
        return 'stripe-card';
    }
}

beforeEach(function () {
    RecordingGateway::$authorizeCalls = [];
    RecordingGateway::$throwOnAuthorize = false;

    Payments::extend('recording', fn () => app(RecordingGateway::class));
    app(PaymentMethodRegistry::class)->add(RecordingMethod::class);
});

/**
 * A PaymentProcessing session pinned against a captured intent: the state
 * the processing page reconciles when no webhook has arrived.
 */
function pinnedRecordingSession(): CheckoutSession
{
    $cart = CheckoutCart::orderable();
    CartSession::use($cart);
    $session = CheckoutCart::session($cart);

    $session->forceFill([
        'status' => PaymentProcessing::$name,
        'payment_intent_ref' => 'pi_recording_pinned',
        'payment_processing_at' => now(),
        'cart_fingerprint' => CheckoutCart::fingerprint($session),
        'meta' => ['payment_method' => 'recording'],
    ])->save();

    return $session->refresh();
}

it('records the payment on the order when reconcile completes without a webhook', function () {
    Exceptions::fake();
    $session = pinnedRecordingSession();

    $this->get(route('lunar.checkout.processing', $session->uuid));

    $session->refresh();
    $order = Order::query()->findOrFail((int) $session->order_reference);

    expect(RecordingGateway::$authorizeCalls)->toHaveCount(1)
        ->and(RecordingGateway::$authorizeCalls[0]['order_id'])->toBe($order->id)
        ->and(RecordingGateway::$authorizeCalls[0]['data']['payment_intent'])->toBe('pi_recording_pinned')
        ->and($session->status)->toBeInstanceOf(Completed::class)
        ->and($order->placed_at)->not->toBeNull()
        ->and($order->transactions()->where('type', 'capture')->count())->toBe(1)
        ->and($order->transactions()->first()->reference)->toBe('pi_recording_pinned');

    Exceptions::assertNothingReported();
});

it('still places the order and completes when the gateway cannot record the payment', function () {
    Exceptions::fake();
    RecordingGateway::$throwOnAuthorize = true;
    $session = pinnedRecordingSession();

    $this->get(route('lunar.checkout.processing', $session->uuid));

    $session->refresh();
    $order = Order::query()->findOrFail((int) $session->order_reference);

    expect($session->status)->toBeInstanceOf(Completed::class)
        ->and($order->placed_at)->not->toBeNull()
        ->and($order->transactions()->count())->toBe(0);

    Exceptions::assertReported(PaymentRecordingException::class);
});

it('does not authorize again when the gateway already placed the order webhook-first', function () {
    $session = pinnedRecordingSession();
    $cart = Cart::query()->findOrFail((int) $session->cart_reference);

    // The webhook path: the gateway's own authorize() placed the order.
    Payments::driver('recording')->cart($cart->calculate())->withData(['payment_intent' => 'pi_recording_pinned'])->authorize();
    RecordingGateway::$authorizeCalls = [];

    $this->get(route('lunar.checkout.processing', $session->uuid));

    $session->refresh();
    $order = Order::query()->findOrFail((int) $session->order_reference);

    expect($session->status)->toBeInstanceOf(Completed::class)
        ->and($order->transactions()->count())->toBe(1)
        ->and(RecordingGateway::$authorizeCalls)->toBeEmpty();
});
