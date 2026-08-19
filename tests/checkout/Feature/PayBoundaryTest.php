<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Checkout\Contracts\PaymentMethodRegistry;
use Lunar\Checkout\PaymentMethods\AbstractPaymentMethod;
use Lunar\Checkout\States\CheckoutSession\Completed;
use Lunar\Checkout\States\CheckoutSession\PaymentProcessing;
use Lunar\Core\Models\Order;
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
    ])->assertStatus(422);

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
