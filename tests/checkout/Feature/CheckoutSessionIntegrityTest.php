<?php

use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\Contracts\Actions\SyncsCheckoutSession;
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Checkout\DataObjects\CartSnapshot;
use Lunar\Checkout\Events\CheckoutPaymentConfirmationFailed;
use Lunar\Checkout\Events\CheckoutSessionInvalidated;
use Lunar\Checkout\Exceptions\PaymentConfirmationException;
use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Checkout\States\CheckoutSession\Cancelled;
use Lunar\Core\Contracts\StorefrontSession;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Tests\Checkout\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function integrityCart(): Cart
{
    CustomerGroup::factory()->create(['default' => true]);

    $channel = Channel::factory()->create(['handle' => 'webstore']);
    $currency = Currency::factory()->create(['code' => 'GBP']);

    app(StorefrontSession::class)
        ->setChannel($channel)
        ->setCurrency($currency);

    return Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
    ]);
}

function snapshotFor(CheckoutSession $session, array $overrides = []): CartSnapshot
{
    return new CartSnapshot(...array_merge([
        'amountSubtotal' => 1500,
        'amountTotal' => 1800,
        'currencyCode' => $session->currency_code,
        'channelHandle' => $session->channel_handle,
        'fingerprint' => hash('sha256', 'fresh'),
        'hasAppliedDiscount' => false,
        'couponCode' => null,
    ], $overrides));
}

it('re-syncs an open session with a guarded write', function () {
    $session = CheckoutSession::factory()->create();

    $synced = app(SyncsCheckoutSession::class)->execute($session, snapshotFor($session));

    expect($synced)->toBeTrue()
        ->and($session->amount_total)->toBe(1800)
        ->and($session->cart_fingerprint)->toBe(hash('sha256', 'fresh'));
});

it('drops a re-sync when the session is no longer open', function () {
    $session = CheckoutSession::factory()->paymentProcessing()->create([
        'amount_total' => 9999,
        'cart_fingerprint' => 'pinned',
    ]);

    $synced = app(SyncsCheckoutSession::class)->execute($session, snapshotFor($session));

    expect($synced)->toBeFalse()
        ->and($session->refresh()->amount_total)->toBe(9999)
        ->and($session->cart_fingerprint)->toBe('pinned');
});

it('invalidates instead of absorbing currency divergence', function () {
    Event::fake([CheckoutSessionInvalidated::class]);

    $session = CheckoutSession::factory()->create();

    $synced = app(SyncsCheckoutSession::class)->execute(
        $session,
        snapshotFor($session, ['currencyCode' => 'EUR']),
    );

    expect($synced)->toBeFalse()
        ->and($session->refresh()->status)->toBeInstanceOf(Cancelled::class)
        ->and($session->active_cart_reference)->toBeNull()
        ->and($session->meta['invalidation_reason'])->toBe('context_diverged');

    Event::assertDispatched(CheckoutSessionInvalidated::class);
});

it('rejects a stale confirmation token at the pay gate', function () {
    Event::fake([CheckoutPaymentConfirmationFailed::class]);

    $cart = integrityCart();
    $driver = app(CheckoutDriver::class);
    $session = $driver->createSession($cart);

    try {
        $driver->assertReadyForPayment($session, 'stale-token');
        $this->fail('Expected PaymentConfirmationException.');
    } catch (PaymentConfirmationException $e) {
        expect($e->reason)->toBe('fingerprint_mismatch');
    }

    expect($session->refresh()->status->getValue())->toBe('open');

    Event::assertDispatched(CheckoutPaymentConfirmationFailed::class);
});

it('stores and reads element bag data on the session', function () {
    $session = CheckoutSession::factory()->create();

    $session->putElementData('gift-message', ['message' => 'Happy birthday']);

    expect($session->refresh()->getElementData('gift-message'))->toBe(['message' => 'Happy birthday'])
        ->and($session->getElementData('missing'))->toBeNull();
});

it('enforces one active session per cart at the database level', function () {
    CheckoutSession::factory()->forCart('77')->create();

    CheckoutSession::factory()->forCart('77')->create();
})->throws(UniqueConstraintViolationException::class);
