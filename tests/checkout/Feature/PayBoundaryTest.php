<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Checkout\Contracts\GuardsPayment;
use Lunar\Checkout\Contracts\PaymentMethodRegistry;
use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Checkout\PaymentMethods\AbstractPaymentMethod;
use Lunar\Checkout\States\CheckoutSession\Completed;
use Lunar\Checkout\States\CheckoutSession\PaymentProcessing;
use Lunar\Core\Contracts\SyncsPaymentIntents;
use Lunar\Core\Facades\Payments;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Order;
use Lunar\Core\PaymentTypes\OfflinePayment;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;

uses(TestCase::class, RefreshDatabase::class);

/**
 * A method needing no gateway confirmation — offline / pay-on-collection /
 * invoice terms (spec 0002 §A).
 */
class SynchronousTestMethod extends AbstractPaymentMethod
{
    public function handle(): string
    {
        return 'on-account';
    }

    public function label(): string
    {
        return 'Pay on account';
    }

    public function driver(): string
    {
        return 'offline';
    }

    public function requiresIntent(): bool
    {
        return false;
    }

    public function component(): string
    {
        return 'offline-notice';
    }
}

/**
 * A gateway-backed method: the async path that pins for confirmation.
 */
class IntentTestMethod extends SynchronousTestMethod
{
    public function handle(): string
    {
        return 'card';
    }

    public function requiresIntent(): bool
    {
        return true;
    }
}

it('completes the session in place for a method that needs no intent', function () {
    app(PaymentMethodRegistry::class)->add(SynchronousTestMethod::class);

    $session = CheckoutCart::session(CheckoutCart::orderable());

    $this->postJson(route('lunar.checkout.pay', $session->uuid), [
        'fingerprint' => CheckoutCart::fingerprint($session),
        'payment_method' => 'on-account',
    ])->assertSuccessful();

    $session->refresh();

    expect($session->status)->toBeInstanceOf(Completed::class)
        ->and($session->order_reference)->not->toBeNull()
        ->and(Order::query()->count())->toBe(1);
});

/**
 * A gateway that records the amount the pay boundary synced onto its intent.
 */
class SyncingTestGateway extends OfflinePayment implements SyncsPaymentIntents
{
    public static ?int $syncedAmount = null;

    public function syncIntent(Cart $cart): void
    {
        static::$syncedAmount = $cart->calculate()->total->value;
    }
}

/**
 * An intent method whose gateway supports amount syncing.
 */
class SyncingIntentTestMethod extends IntentTestMethod
{
    public function handle(): string
    {
        return 'sync-card';
    }

    public function driver(): string
    {
        return 'sync-test';
    }
}

it('syncs the gateway intent to the live total before pinning', function () {
    Payments::extend('sync-test', fn () => app(SyncingTestGateway::class));
    SyncingTestGateway::$syncedAmount = null;

    app(PaymentMethodRegistry::class)->add(SyncingIntentTestMethod::class);

    $session = CheckoutCart::session(CheckoutCart::orderable());

    $this->postJson(route('lunar.checkout.pay', $session->uuid), [
        'fingerprint' => CheckoutCart::fingerprint($session),
        'payment_method' => 'sync-card',
    ])->assertSuccessful();

    $session->refresh();

    // The intent was created at payment-form mount time; the basket may have
    // changed since. The boundary must hand the gateway the amount it pins.
    expect(SyncingTestGateway::$syncedAmount)->toBe($session->amount_total)
        ->and($session->status)->toBeInstanceOf(PaymentProcessing::class);
});

it('still pins for confirmation when the method needs an intent', function () {
    app(PaymentMethodRegistry::class)->add(IntentTestMethod::class);

    $session = CheckoutCart::session(CheckoutCart::orderable());

    $this->postJson(route('lunar.checkout.pay', $session->uuid), [
        'fingerprint' => CheckoutCart::fingerprint($session),
        'payment_method' => 'card',
    ])->assertSuccessful();

    $session->refresh();

    expect($session->status)->toBeInstanceOf(PaymentProcessing::class)
        ->and(Order::query()->count())->toBe(0);
});

it('completes a zero-total cart synchronously even for an intent method', function () {
    app(PaymentMethodRegistry::class)->add(IntentTestMethod::class);

    // Nothing to charge — spec 0002 §A: a zero total forces the synchronous
    // path regardless of the method's declared capability.
    $session = CheckoutCart::session(CheckoutCart::orderable(unitPrice: 0));

    $this->postJson(route('lunar.checkout.pay', $session->uuid), [
        'fingerprint' => CheckoutCart::fingerprint($session),
        'payment_method' => 'card',
    ])->assertSuccessful();

    expect($session->refresh()->status)->toBeInstanceOf(Completed::class);
});

it('refuses to complete synchronously on a stale fingerprint', function () {
    app(PaymentMethodRegistry::class)->add(SynchronousTestMethod::class);

    $session = CheckoutCart::session(CheckoutCart::orderable());

    $this->postJson(route('lunar.checkout.pay', $session->uuid), [
        'fingerprint' => 'not-the-current-state',
        'payment_method' => 'on-account',
    ])->assertStatus(422)
        // Customer-facing copy, not the developer reason code.
        ->assertJsonPath('errors.fingerprint.0', 'Your order changed while you were checking out. Check the details above and try again.');

    expect($session->refresh()->status)->not->toBeInstanceOf(Completed::class)
        ->and(Order::query()->count())->toBe(0);
});

it('rejects a payment method that is not registered', function () {
    $session = CheckoutCart::session(CheckoutCart::orderable());

    $this->postJson(route('lunar.checkout.pay', $session->uuid), [
        'fingerprint' => CheckoutCart::fingerprint($session),
        'payment_method' => 'carrier-pigeon',
    ])->assertStatus(422);
});

it('creates only one order when completion is attempted twice', function () {
    app(PaymentMethodRegistry::class)->add(SynchronousTestMethod::class);

    $session = CheckoutCart::session(CheckoutCart::orderable());
    $fingerprint = CheckoutCart::fingerprint($session);

    $this->postJson(route('lunar.checkout.pay', $session->uuid), [
        'fingerprint' => $fingerprint,
        'payment_method' => 'on-account',
    ])->assertSuccessful();

    // Re-entering completion is what the webhook, the reconciliation sweep and
    // a double submit all do; the guarded transition means one order survives.
    app(CheckoutDriver::class)->complete($session->refresh(), $fingerprint);

    expect(Order::query()->count())->toBe(1)
        ->and($session->refresh()->order_reference)->toBe((string) Order::query()->value('id'));
});

it('refuses a pay request once the session has completed', function () {
    app(PaymentMethodRegistry::class)->add(SynchronousTestMethod::class);

    $session = CheckoutCart::session(CheckoutCart::orderable());
    $fingerprint = CheckoutCart::fingerprint($session);

    $this->postJson(route('lunar.checkout.pay', $session->uuid), [
        'fingerprint' => $fingerprint,
        'payment_method' => 'on-account',
    ])->assertSuccessful();

    // Completion releases the cart session, so the capability no longer
    // resolves to a live basket — a replayed submit is not an owner.
    $this->postJson(route('lunar.checkout.pay', $session->uuid), [
        'fingerprint' => $fingerprint,
        'payment_method' => 'on-account',
    ])->assertForbidden();

    expect(Order::query()->count())->toBe(1);
});

/**
 * A synchronous method that refuses to proceed until a flag is cleared, the
 * shape of "enter your PO reference first" (spec 0014 §C).
 */
class GuardedTestMethod extends SynchronousTestMethod implements GuardsPayment
{
    public static ?string $blocker = 'Enter your purchase order reference to place this order on account.';

    public function handle(): string
    {
        return 'guarded';
    }

    public function paymentBlocker(CheckoutSession $session, Cart $cart): ?string
    {
        return static::$blocker;
    }
}

it('projects each method\'s payment blocker', function () {
    GuardedTestMethod::$blocker = 'Enter your purchase order reference to place this order on account.';
    app(PaymentMethodRegistry::class)->add(SynchronousTestMethod::class)->add(GuardedTestMethod::class);

    $session = CheckoutCart::session(CheckoutCart::orderable());

    $response = $this->get(route('lunar.checkout.show', $session->uuid), ['X-Inertia' => 'true'])
        ->assertOk();

    expect($response->json('props.checkout.paymentMethods.0.paymentBlocker'))->toBeNull();
    expect($response->json('props.checkout.paymentMethods.1.paymentBlocker'))->toBe('Enter your purchase order reference to place this order on account.');
});

it('refuses to pay with a method whose guard blocks, and proceeds once it clears', function () {
    GuardedTestMethod::$blocker = 'Enter your purchase order reference to place this order on account.';
    app(PaymentMethodRegistry::class)->add(GuardedTestMethod::class);

    $session = CheckoutCart::session(CheckoutCart::orderable());

    $this->postJson(route('lunar.checkout.pay', $session->uuid), [
        'fingerprint' => CheckoutCart::fingerprint($session),
        'payment_method' => 'guarded',
    ])->assertStatus(422)
        ->assertJsonPath('errors.payment_method.0', 'Enter your purchase order reference to place this order on account.');

    expect($session->refresh()->status)->not->toBeInstanceOf(Completed::class);

    GuardedTestMethod::$blocker = null;

    $this->postJson(route('lunar.checkout.pay', $session->uuid), [
        'fingerprint' => CheckoutCart::fingerprint($session),
        'payment_method' => 'guarded',
    ])->assertSuccessful();

    expect($session->refresh()->status)->toBeInstanceOf(Completed::class);
});

it('records the chosen method handle on the session for both paths', function () {
    app(PaymentMethodRegistry::class)->add(SynchronousTestMethod::class)->add(IntentTestMethod::class);

    $sync = CheckoutCart::session(CheckoutCart::orderable());
    $this->postJson(route('lunar.checkout.pay', $sync->uuid), [
        'fingerprint' => CheckoutCart::fingerprint($sync),
        'payment_method' => 'on-account',
    ])->assertSuccessful();

    expect($sync->refresh()->meta['payment_handle'])->toBe('on-account');

    $async = CheckoutCart::session(CheckoutCart::orderable());
    $this->postJson(route('lunar.checkout.pay', $async->uuid), [
        'fingerprint' => CheckoutCart::fingerprint($async),
        'payment_method' => 'card',
    ])->assertSuccessful();

    expect($async->refresh()->meta['payment_handle'])->toBe('card')
        ->and($async->status)->toBeInstanceOf(PaymentProcessing::class);
});
