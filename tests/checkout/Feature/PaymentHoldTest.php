<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\Contracts\PaymentMethodRegistry;
use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Checkout\PaymentMethods\AbstractPaymentMethod;
use Lunar\Checkout\States\CheckoutSession\Completed;
use Lunar\Checkout\States\CheckoutSession\Open;
use Lunar\Checkout\States\CheckoutSession\PaymentProcessing;
use Lunar\Core\Contracts\CreatesPaymentIntents;
use Lunar\Core\Contracts\SupportsPaymentHolds;
use Lunar\Core\Contracts\SupportsPaymentIntents;
use Lunar\Core\DataObjects\HoldDescription;
use Lunar\Core\DataObjects\PaymentIntentDescriptor;
use Lunar\Core\Enums\HoldAdjustment;
use Lunar\Core\Enums\PaymentIntentStatus;
use Lunar\Core\Facades\CartSession;
use Lunar\Core\Facades\Payments;
use Lunar\Core\Models\Cart;
use Lunar\Core\PaymentTypes\OfflinePayment;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;

uses(TestCase::class, RefreshDatabase::class);

/**
 * A gateway that can both create standard intents and authorise holds, the
 * way Stripe will once Task 3's flavour-keyed intents land here. Calls are
 * recorded on public static arrays, the way {@see PaymentIntentReuseTest}
 * doesn't need to (it only asserts driver behaviour) but the hold-routing
 * assertions here do.
 */
class FakeHoldGateway extends OfflinePayment implements CreatesPaymentIntents, SupportsPaymentHolds, SupportsPaymentIntents
{
    /** @var array<int, int> */
    public static array $createIntentCalls = [];

    /** @var array<int, int> */
    public static array $createHoldCalls = [];

    /** @var array<int, string> */
    public static array $voidedReferences = [];

    /** @var array<int, array{reference: string, amount: int}> */
    public static array $captureCalls = [];

    /** @var array<int, array{reference: string, amount: int}> */
    public static array $refundCalls = [];

    public static int $holdSequence = 0;

    public static HoldAdjustment $adjustOutcome = HoldAdjustment::Ok;

    public static PaymentIntentStatus $fetchStatus = PaymentIntentStatus::RequiresCapture;

    public static bool $throwOnCapture = false;

    public function createIntent(Cart $cart): PaymentIntentDescriptor
    {
        static::$createIntentCalls[] = $cart->id;

        return new PaymentIntentDescriptor('pi_fake_'.$cart->id);
    }

    public function createHold(Cart $cart): PaymentIntentDescriptor
    {
        static::$createHoldCalls[] = $cart->id;

        return new PaymentIntentDescriptor('hold_fake_'.$cart->id.'_'.(++static::$holdSequence));
    }

    public function describeHold(string $reference): ?HoldDescription
    {
        return null;
    }

    public function adjustHold(string $reference, int $amountMinor): HoldAdjustment
    {
        return static::$adjustOutcome;
    }

    public function captureHold(string $reference, int $amountMinor): void
    {
        if (static::$throwOnCapture) {
            throw new RuntimeException('fake gateway capture failed');
        }

        static::$captureCalls[] = ['reference' => $reference, 'amount' => $amountMinor];
    }

    public function fetchIntent(string $reference): PaymentIntentStatus
    {
        return static::$fetchStatus;
    }

    public function voidIntent(string $reference): void
    {
        static::$voidedReferences[] = $reference;
    }

    public function refundIntent(string $reference, int $amountMinor, string $idempotencyKey): string
    {
        static::$refundCalls[] = ['reference' => $reference, 'amount' => $amountMinor];

        return 'refund_fake_'.$reference;
    }
}

/**
 * A gateway that can only create standard intents, no hold capability, the
 * way most drivers will stay.
 */
class IntentOnlyGateway extends OfflinePayment implements CreatesPaymentIntents
{
    public function createIntent(Cart $cart): PaymentIntentDescriptor
    {
        return new PaymentIntentDescriptor('pi_intent_only_'.$cart->id);
    }
}

class FakeHoldMethod extends AbstractPaymentMethod
{
    public function handle(): string
    {
        return 'fake';
    }

    public function label(): string
    {
        return 'Fake wallet';
    }

    public function driver(): string
    {
        return 'fake-hold';
    }

    public function requiresIntent(): bool
    {
        return true;
    }

    public function component(): string
    {
        return 'fake-wallet';
    }
}

class IntentOnlyMethod extends AbstractPaymentMethod
{
    public function handle(): string
    {
        return 'intent-only';
    }

    public function label(): string
    {
        return 'Card';
    }

    public function driver(): string
    {
        return 'intent-only';
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

function registerFakeHoldGateway(): void
{
    Payments::extend('fake-hold', fn () => app(FakeHoldGateway::class));
    app(PaymentMethodRegistry::class)->add(FakeHoldMethod::class);
}

function registerIntentOnlyGateway(): void
{
    Payments::extend('intent-only', fn () => app(IntentOnlyGateway::class));
    app(PaymentMethodRegistry::class)->add(IntentOnlyMethod::class);
}

function mintOpenSessionWithMethod(): CheckoutSession
{
    registerFakeHoldGateway();

    $cart = CheckoutCart::orderable();
    CartSession::use($cart);

    return CheckoutCart::session($cart);
}

function mintOpenSessionWithIntentOnlyMethod(): CheckoutSession
{
    registerIntentOnlyGateway();

    $cart = CheckoutCart::orderable();
    CartSession::use($cart);

    return CheckoutCart::session($cart);
}

/**
 * An Open session already carrying a live hold, the way Task 4's
 * payment-intent endpoint leaves it (`meta.payment_intent_mode === 'hold'`,
 * `payment_intent_ref` set to the gateway's hold reference).
 */
function mintHoldSession(): CheckoutSession
{
    $session = mintOpenSessionWithMethod();

    test()->postJson(route('lunar.checkout.payment-intent.store', $session->uuid), [
        'payment_method' => 'fake',
        'mode' => 'hold',
    ])->assertOk();

    return $session->refresh();
}

/**
 * A `PaymentProcessing` session pinned against {@see FakeHoldGateway}'s
 * standard (non-hold) intent: the pre-existing manual-capture policy path,
 * which reconcile must keep completing without ever calling captureHold.
 */
function mintPinnedStandardSession(): CheckoutSession
{
    registerFakeHoldGateway();

    $cart = CheckoutCart::orderable();
    CartSession::use($cart);
    $session = CheckoutCart::session($cart);

    $session->forceFill([
        'status' => PaymentProcessing::$name,
        'payment_intent_ref' => 'pi_fake_pinned',
        'payment_processing_at' => now(),
        'meta' => ['payment_method' => 'fake-hold'],
    ])->save();

    return $session->refresh();
}

/**
 * A `PaymentProcessing` session pinned against a hold (`payment_intent_mode`
 * === 'hold'), the state reconcile/release act on after the pay boundary has
 * already pinned a hold-mode session.
 */
function mintPinnedHoldSession(): CheckoutSession
{
    registerFakeHoldGateway();

    $cart = CheckoutCart::orderable();
    CartSession::use($cart);
    $session = CheckoutCart::session($cart);

    $session->forceFill([
        'status' => PaymentProcessing::$name,
        'payment_intent_ref' => 'hold_fake_pinned',
        'payment_processing_at' => now(),
        'meta' => ['payment_method' => 'fake-hold', 'payment_intent_mode' => 'hold'],
    ])->save();

    return $session->refresh();
}

/**
 * Adds a line to the session's live cart so its fingerprint diverges from the
 * one pinned at the pay boundary, forcing `driver->complete()` to throw
 * `PaymentConfirmationException('fingerprint_mismatch')`: the same
 * cart-changed-after-pin condition the existing refund-path behaviour guards.
 */
function breakCartSoCompletionFails(CheckoutSession $session): void
{
    CheckoutCart::addLine(Cart::query()->findOrFail((int) $session->cart_reference));
}

beforeEach(function () {
    FakeHoldGateway::$createIntentCalls = [];
    FakeHoldGateway::$createHoldCalls = [];
    FakeHoldGateway::$voidedReferences = [];
    FakeHoldGateway::$captureCalls = [];
    FakeHoldGateway::$refundCalls = [];
    FakeHoldGateway::$holdSequence = 0;
    FakeHoldGateway::$adjustOutcome = HoldAdjustment::Ok;
    FakeHoldGateway::$fetchStatus = PaymentIntentStatus::RequiresCapture;
    FakeHoldGateway::$throwOnCapture = false;
});

it('routes mode hold to createHold and records the mode on the session', function () {
    $session = mintOpenSessionWithMethod();

    $response = $this->postJson(route('lunar.checkout.payment-intent.store', $session->uuid), [
        'payment_method' => 'fake',
        'mode' => 'hold',
    ]);

    $response->assertOk();
    expect(FakeHoldGateway::$createHoldCalls)->toHaveCount(1)
        ->and(FakeHoldGateway::$createIntentCalls)->toBeEmpty();

    $session->refresh();
    expect($session->meta['payment_intent_mode'] ?? null)->toBe('hold');
});

it('refuses mode hold for a driver without the capability', function () {
    $session = mintOpenSessionWithIntentOnlyMethod();

    $this->postJson(route('lunar.checkout.payment-intent.store', $session->uuid), [
        'payment_method' => 'intent-only',
        'mode' => 'hold',
    ])->assertUnprocessable();
});

it('renew voids the existing hold before minting a fresh one', function () {
    $session = mintOpenSessionWithMethod();

    $this->postJson(route('lunar.checkout.payment-intent.store', $session->uuid), [
        'payment_method' => 'fake', 'mode' => 'hold',
    ])->assertOk();

    $this->postJson(route('lunar.checkout.payment-intent.store', $session->uuid), [
        'payment_method' => 'fake', 'mode' => 'hold', 'renew' => true,
    ])->assertOk();

    expect(FakeHoldGateway::$voidedReferences)->toHaveCount(1)
        ->and(FakeHoldGateway::$createHoldCalls)->toHaveCount(2);
});

it('a standard intent request clears a stale hold mode', function () {
    $session = mintOpenSessionWithMethod();

    $this->postJson(route('lunar.checkout.payment-intent.store', $session->uuid), [
        'payment_method' => 'fake', 'mode' => 'hold',
    ])->assertOk();

    $this->postJson(route('lunar.checkout.payment-intent.store', $session->uuid), [
        'payment_method' => 'fake',
    ])->assertOk();

    expect($session->refresh()->meta['payment_intent_mode'] ?? null)->toBeNull();
});

it('adjusts the hold before pinning and rejects with NeedsReauthorization while still Open', function () {
    $session = mintHoldSession();
    FakeHoldGateway::$adjustOutcome = HoldAdjustment::NeedsReauthorization;

    // Raise the live total past what the hold was authorised for.
    CheckoutCart::addLine(Cart::query()->findOrFail((int) $session->cart_reference), 5000);

    $response = $this->postJson(route('lunar.checkout.pay', $session->uuid), [
        'payment_method' => 'fake',
        'fingerprint' => CheckoutCart::fingerprint($session),
    ]);

    $response->assertUnprocessable();
    expect($session->refresh()->status)->toBeInstanceOf(Open::class);
});

it('pins a hold session and reconcile captures then completes', function () {
    $session = mintHoldSession();
    FakeHoldGateway::$fetchStatus = PaymentIntentStatus::RequiresCapture;

    $this->postJson(route('lunar.checkout.pay', $session->uuid), [
        'payment_method' => 'fake',
        'fingerprint' => CheckoutCart::fingerprint($session),
    ])->assertOk()
        ->assertJsonPath('pinned', true)
        ->assertJsonPath('processing', route('lunar.checkout.processing', $session->uuid));

    expect($session->refresh()->status)->toBeInstanceOf(PaymentProcessing::class);

    $this->get(route('lunar.checkout.processing', $session->uuid));

    expect(FakeHoldGateway::$captureCalls)->toHaveCount(1)
        ->and(FakeHoldGateway::$captureCalls[0]['amount'])->toBe($session->refresh()->amount_total)
        ->and($session->status)->toBeInstanceOf(Completed::class);
});

it('does not capture policy-mode requires_capture sessions (pre-existing manual policy)', function () {
    $session = mintPinnedStandardSession();
    FakeHoldGateway::$fetchStatus = PaymentIntentStatus::RequiresCapture;

    $this->get(route('lunar.checkout.processing', $session->uuid));

    expect(FakeHoldGateway::$captureCalls)->toBeEmpty()
        ->and($session->refresh()->status)->toBeInstanceOf(Completed::class);
});

it('reopens without void or refund when the hold capture itself fails, keeping the hold intact', function () {
    $session = mintPinnedHoldSession();
    FakeHoldGateway::$fetchStatus = PaymentIntentStatus::RequiresCapture;
    FakeHoldGateway::$throwOnCapture = true;

    $this->get(route('lunar.checkout.processing', $session->uuid));

    expect(FakeHoldGateway::$captureCalls)->toBeEmpty()
        ->and(FakeHoldGateway::$voidedReferences)->toBeEmpty()
        ->and(FakeHoldGateway::$refundCalls)->toBeEmpty();

    $session->refresh();
    expect($session->status)->toBeInstanceOf(Open::class)
        ->and($session->payment_intent_ref)->toBe('hold_fake_pinned');
});

it('refunds, never voids, when completion fails after the hold was already captured', function () {
    $session = mintPinnedHoldSession();
    FakeHoldGateway::$fetchStatus = PaymentIntentStatus::RequiresCapture;

    breakCartSoCompletionFails($session);

    $this->get(route('lunar.checkout.processing', $session->uuid));

    expect(FakeHoldGateway::$captureCalls)->toHaveCount(1)
        ->and(FakeHoldGateway::$refundCalls)->toHaveCount(1)
        ->and(FakeHoldGateway::$voidedReferences)->toBeEmpty();

    expect($session->refresh()->status)->toBeInstanceOf(Open::class);
});

it('release reopens a pinned hold session keeping the hold intact', function () {
    $session = mintPinnedHoldSession();
    FakeHoldGateway::$fetchStatus = PaymentIntentStatus::RequiresCapture;

    $this->postJson(route('lunar.checkout.payment-release', $session->uuid))
        ->assertOk()
        ->assertJsonPath('outcome', 'released');

    $session->refresh();
    expect($session->status)->toBeInstanceOf(Open::class)
        ->and($session->payment_intent_ref)->not->toBeNull();
    expect(FakeHoldGateway::$captureCalls)->toBeEmpty();
});
