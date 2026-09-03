<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\Contracts\PaymentMethodRegistry;
use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Checkout\PaymentMethods\AbstractPaymentMethod;
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

    public static int $holdSequence = 0;

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
        return HoldAdjustment::Ok;
    }

    public function captureHold(string $reference, int $amountMinor): void
    {
        //
    }

    public function fetchIntent(string $reference): PaymentIntentStatus
    {
        return PaymentIntentStatus::RequiresCapture;
    }

    public function voidIntent(string $reference): void
    {
        static::$voidedReferences[] = $reference;
    }

    public function refundIntent(string $reference, int $amountMinor, string $idempotencyKey): string
    {
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

beforeEach(function () {
    FakeHoldGateway::$createIntentCalls = [];
    FakeHoldGateway::$createHoldCalls = [];
    FakeHoldGateway::$voidedReferences = [];
    FakeHoldGateway::$holdSequence = 0;
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
