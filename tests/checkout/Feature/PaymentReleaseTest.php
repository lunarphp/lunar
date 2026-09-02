<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\States\CheckoutSession\Completed;
use Lunar\Checkout\States\CheckoutSession\Open;
use Lunar\Checkout\States\CheckoutSession\PaymentProcessing;
use Lunar\Core\Contracts\SupportsPaymentIntents;
use Lunar\Core\Enums\PaymentIntentStatus;
use Lunar\Core\Facades\Payments;
use Lunar\Core\Models\Order;
use Lunar\Core\PaymentTypes\OfflinePayment;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;

uses(TestCase::class, RefreshDatabase::class);

/**
 * A gateway whose intent outcome the test scripts. Extends the offline type so
 * authorize()/refund() satisfy the abstract payment surface.
 */
class ReleaseTestGateway extends OfflinePayment implements SupportsPaymentIntents
{
    public static PaymentIntentStatus $status = PaymentIntentStatus::Pending;

    public static bool $throwOnFetch = false;

    public function fetchIntent(string $reference): PaymentIntentStatus
    {
        if (static::$throwOnFetch) {
            throw new RuntimeException('gateway unreachable');
        }

        return static::$status;
    }

    public function voidIntent(string $reference): void {}

    public function refundIntent(string $reference, int $amountMinor, string $idempotencyKey): string
    {
        return 're_test';
    }
}

function pinnedSession(array $overrides = [])
{
    $session = CheckoutCart::session(CheckoutCart::orderable());

    $session->forceFill(array_merge([
        'status' => PaymentProcessing::$name,
        'payment_intent_ref' => 'pi_test_123',
        'payment_processing_at' => now(),
        'meta' => ['payment_method' => 'release-test'],
    ], $overrides))->save();

    return $session->refresh();
}

beforeEach(function () {
    Payments::extend('release-test', fn () => app(ReleaseTestGateway::class));
    ReleaseTestGateway::$status = PaymentIntentStatus::Pending;
    ReleaseTestGateway::$throwOnFetch = false;
});

it('reopens a pinned session when the gateway holds no captured money', function () {
    $session = pinnedSession();

    $this->postJson(route('lunar.checkout.payment-release', $session->uuid))
        ->assertOk()
        ->assertJsonPath('released', true)
        ->assertJsonPath('outcome', 'released')
        ->assertJsonStructure(['fingerprint']);

    $session->refresh();

    // Open again for a retry, and the intent reference survives: the mounted
    // payment element is still bound to it.
    expect($session->status)->toBeInstanceOf(Open::class)
        ->and($session->payment_intent_ref)->toBe('pi_test_123');
});

it('completes instead of releasing when the money was captured', function () {
    ReleaseTestGateway::$status = PaymentIntentStatus::Captured;

    $session = pinnedSession();

    $this->postJson(route('lunar.checkout.payment-release', $session->uuid))
        ->assertOk()
        ->assertJsonPath('outcome', 'completed')
        ->assertJsonPath('released', false);

    expect($session->refresh()->status)->toBeInstanceOf(Completed::class)
        ->and(Order::query()->count())->toBe(1);
});

it('stays frozen when the gateway outcome cannot be confirmed', function () {
    ReleaseTestGateway::$throwOnFetch = true;

    $session = pinnedSession();

    $this->postJson(route('lunar.checkout.payment-release', $session->uuid))
        ->assertOk()
        ->assertJsonPath('outcome', 'unconfirmed')
        ->assertJsonPath('released', false);

    expect($session->refresh()->status)->toBeInstanceOf(PaymentProcessing::class);
});

it('is a no-op on a session that is not pinned', function () {
    $session = CheckoutCart::session(CheckoutCart::orderable());

    $this->postJson(route('lunar.checkout.payment-release', $session->uuid))
        ->assertOk()
        ->assertJsonPath('outcome', 'not-applicable')
        ->assertJsonPath('released', false);

    expect($session->refresh()->status)->toBeInstanceOf(Open::class);
});

it('maps a frozen write to a 409 with customer copy, not a 500', function () {
    $session = pinnedSession();

    $this->postJson(route('lunar.checkout.billing-address.store', $session->uuid), [
        'first_name' => 'Ada', 'last_name' => 'Lovelace', 'line1' => '1 Test St',
        'city' => 'London', 'postcode' => 'SW1A 2AA', 'country_code' => 'GB',
    ])->assertStatus(409)
        ->assertJsonPath('reason', 'frozen')
        ->assertJsonPath('message', 'This checkout is busy finishing a payment attempt. Wait a moment and try again.');
});

it('settles a captured pinned session when the customer views it', function () {
    ReleaseTestGateway::$status = PaymentIntentStatus::Captured;

    $session = pinnedSession([
        'payment_processing_at' => now()->subSeconds(10),
        'success_url' => 'https://store.test/thanks',
    ]);

    // The customer's poll IS the webhook-less completion signal.
    $this->get(route('lunar.checkout.show', $session->uuid))
        ->assertRedirect('https://store.test/thanks');

    expect($session->refresh()->status)->toBeInstanceOf(Completed::class)
        ->and(Order::query()->count())->toBe(1);
});

it('does not settle a freshly pinned session on view', function () {
    ReleaseTestGateway::$status = PaymentIntentStatus::Captured;

    // Inside the age gate: the immediate post-confirm poll must not race the
    // webhook with a second settlement attempt.
    $session = pinnedSession(['payment_processing_at' => now()]);

    // X-Inertia: JSON page object, so testbench need not render the root
    // blade (no published Vite build there) — same as CheckoutRouteTest.
    $this->get(route('lunar.checkout.show', $session->uuid), ['X-Inertia' => 'true'])->assertOk();

    expect($session->refresh()->status)->toBeInstanceOf(PaymentProcessing::class);
});

it('reopens a failed pinned session when the customer views it', function () {
    ReleaseTestGateway::$status = PaymentIntentStatus::Failed;

    $session = pinnedSession(['payment_processing_at' => now()->subSeconds(10)]);

    $this->get(route('lunar.checkout.show', $session->uuid), ['X-Inertia' => 'true'])->assertOk();

    expect($session->refresh()->status)->toBeInstanceOf(Open::class);
});

it('processing settles a captured session and forwards to the success url', function () {
    ReleaseTestGateway::$status = PaymentIntentStatus::Captured;

    $session = pinnedSession(['success_url' => 'https://store.test/thanks']);

    $this->get(route('lunar.checkout.processing', $session->uuid))
        ->assertRedirect('https://store.test/thanks')
        // The confirmation handover: the store's success page reads this to
        // load the placed order. put(), not flash - it must survive the
        // processing page's probe fetch and a refresh of the success page.
        ->assertSessionHas('lunar.checkout.completed.uuid', $session->uuid)
        ->assertSessionHas('lunar.checkout.completed.order_reference', (string) Order::query()->firstOrFail()->id);

    expect($session->refresh()->status)->toBeInstanceOf(Completed::class)
        ->and(Order::query()->count())->toBe(1);
});

it('processing falls back to the configured store success url', function () {
    config()->set('lunar.checkout.urls.success', 'https://store.test/order-complete');

    ReleaseTestGateway::$status = PaymentIntentStatus::Captured;

    $session = pinnedSession();

    $this->get(route('lunar.checkout.processing', $session->uuid))
        ->assertRedirect('https://store.test/order-complete');
});

it('processing sends a failed payment back to the checkout to retry', function () {
    ReleaseTestGateway::$status = PaymentIntentStatus::Failed;

    $session = pinnedSession();

    $this->get(route('lunar.checkout.processing', $session->uuid))
        ->assertRedirect(route('lunar.checkout.show', $session->uuid).'?payment=failed');

    expect($session->refresh()->status)->toBeInstanceOf(Open::class);
});

it('processing renders the polling page while the outcome is in flight', function () {
    ReleaseTestGateway::$status = PaymentIntentStatus::Pending;

    $session = pinnedSession();

    $this->get(route('lunar.checkout.processing', $session->uuid), ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('component', 'Processing')
        ->assertJsonPath('props.pollUrl', route('lunar.checkout.processing', $session->uuid));

    expect($session->refresh()->status)->toBeInstanceOf(PaymentProcessing::class);
});

it('processing redirects an already completed session straight to success', function () {
    $session = pinnedSession();
    $session->forceFill(['status' => Completed::$name, 'success_url' => 'https://store.test/thanks'])->save();

    $this->get(route('lunar.checkout.processing', $session->uuid))
        ->assertRedirect('https://store.test/thanks');
});
