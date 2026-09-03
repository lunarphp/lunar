<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Core\DataObjects\HoldDescription;
use Lunar\Core\Enums\PaymentIntentStatus;
use Lunar\Core\Facades\CartSession;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    FakeHoldGateway::$describeOutcome = null;
});

/**
 * An Open session carrying a live hold (the way the wallet sheet leaves it):
 * registers {@see FakeHoldGateway} (shared with PaymentHoldTest) and mints
 * the hold through the real payment-intent endpoint.
 */
function mintOpenHoldSession(): CheckoutSession
{
    registerFakeHoldGateway();

    $cart = CheckoutCart::orderable();
    CartSession::use($cart);
    $session = CheckoutCart::session($cart);

    test()->postJson(route('lunar.checkout.payment-intent.store', $session->uuid), [
        'payment_method' => 'fake',
        'mode' => 'hold',
    ])->assertOk();

    return $session->refresh();
}

/**
 * An Open session with no payment intent at all.
 */
function mintOpenSession(): CheckoutSession
{
    registerFakeHoldGateway();

    $cart = CheckoutCart::orderable();
    CartSession::use($cart);

    return CheckoutCart::session($cart);
}

/**
 * An Open session carrying a standard (non-hold) intent.
 */
function mintOpenSessionWithStandardIntent(): CheckoutSession
{
    registerFakeHoldGateway();

    $cart = CheckoutCart::orderable();
    CartSession::use($cart);
    $session = CheckoutCart::session($cart);

    test()->postJson(route('lunar.checkout.payment-intent.store', $session->uuid), [
        'payment_method' => 'fake',
    ])->assertOk();

    return $session->refresh();
}

it('renders the confirm page for a session with a live hold', function () {
    $session = mintOpenHoldSession();
    FakeHoldGateway::$describeOutcome = new HoldDescription(
        status: PaymentIntentStatus::RequiresCapture,
        amountMinor: 2000,
        walletLabel: 'Apple Pay',
    );

    $this->get(route('lunar.checkout.confirm', $session->uuid), ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('component', 'ExpressConfirm')
        ->assertJsonPath('props.checkout.hold.amountAuthorised', 2000)
        ->assertJsonPath('props.checkout.hold.walletLabel', 'Apple Pay');
});

it('redirects to the checkout when there is no hold', function () {
    $session = mintOpenSession();

    $this->get(route('lunar.checkout.confirm', $session->uuid))
        ->assertRedirect(route('lunar.checkout.show', $session->uuid));
});

it('redirects when the intent is not hold mode', function () {
    $session = mintOpenSessionWithStandardIntent();

    $this->get(route('lunar.checkout.confirm', $session->uuid))
        ->assertRedirect(route('lunar.checkout.show', $session->uuid));
});

it('redirects when the gateway does not verify requires-capture', function () {
    $session = mintOpenHoldSession();
    FakeHoldGateway::$describeOutcome = new HoldDescription(
        status: PaymentIntentStatus::Voided, amountMinor: 2000, walletLabel: null,
    );

    $this->get(route('lunar.checkout.confirm', $session->uuid))
        ->assertRedirect(route('lunar.checkout.show', $session->uuid));
});

it('redirects an unswept expired session even though its row still reads Open', function () {
    $session = mintOpenHoldSession();
    FakeHoldGateway::$describeOutcome = new HoldDescription(
        status: PaymentIntentStatus::RequiresCapture,
        amountMinor: 2000,
        walletLabel: 'Apple Pay',
    );

    // Expiry is swept lazily: backdate expires_at directly, without
    // transitioning `status` away from Open, the way a real session sits
    // between its window closing and the next sweep.
    $session->forceFill(['expires_at' => now()->subMinute()])->save();

    $this->get(route('lunar.checkout.confirm', $session->uuid))
        ->assertRedirect(route('lunar.checkout.show', $session->uuid));
});
