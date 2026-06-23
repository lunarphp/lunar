<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Checkout\Events\CheckoutSessionSuperseded;
use Lunar\Checkout\Exceptions\CheckoutSessionConflictException;
use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Checkout\States\CheckoutSession\Cancelled;
use Lunar\Checkout\States\CheckoutSession\Expired;
use Lunar\Checkout\States\CheckoutSession\Open;
use Lunar\Core\Contracts\StorefrontSession;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Tests\Checkout\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function makeCart(): Cart
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

it('creates a session from a cart via the lunar driver', function () {
    $cart = makeCart();

    $session = app(CheckoutDriver::class)->createSession($cart);

    expect($session)->toBeInstanceOf(CheckoutSession::class)
        ->and($session->cart_reference)->toBe((string) $cart->id)
        ->and($session->active_cart_reference)->toBe((string) $cart->id)
        ->and($session->status)->toBeInstanceOf(Open::class)
        ->and($session->uuid)->not->toBeEmpty();

    $this->assertDatabaseHas('lunar_checkout_sessions', [
        'cart_reference' => (string) $cart->id,
        'status' => 'open',
    ]);
});

it('pins the snapshot and the driver fingerprint at creation', function () {
    $cart = makeCart();
    $cart->calculate();

    $session = app(CheckoutDriver::class)->createSession($cart);

    expect($session->currency_code)->toBe('GBP')
        ->and($session->channel_handle)->toBe('webstore')
        ->and($session->amount_subtotal)->toBe($cart->subTotal?->value ?? 0)
        ->and($session->amount_total)->toBe($cart->total?->value ?? 0)
        ->and($session->cart_fingerprint)->not->toBeEmpty()
        ->and($session->cart_fingerprint)->toBe(app(CheckoutDriver::class)->fingerprint($session));
});

it('supersedes a prior open session for the same cart', function () {
    Event::fake([CheckoutSessionSuperseded::class]);

    $cart = makeCart();
    $driver = app(CheckoutDriver::class);

    $first = $driver->createSession($cart);
    $second = $driver->createSession($cart);

    expect($first->refresh()->status)->toBeInstanceOf(Cancelled::class)
        ->and($first->active_cart_reference)->toBeNull()
        ->and($second->status)->toBeInstanceOf(Open::class)
        ->and($second->active_cart_reference)->toBe((string) $cart->id);

    Event::assertDispatched(CheckoutSessionSuperseded::class);
});

it('resumes the live open session instead of churning on resolve-or-create', function () {
    Event::fake([CheckoutSessionSuperseded::class]);

    $cart = makeCart();
    $driver = app(CheckoutDriver::class);

    $first = $driver->resolveOrCreateSession($cart);
    $second = $driver->resolveOrCreateSession($cart);

    expect($second->id)->toBe($first->id)
        ->and($second->status)->toBeInstanceOf(Open::class)
        ->and(CheckoutSession::query()->where('cart_reference', (string) $cart->id)->count())->toBe(1);

    Event::assertNotDispatched(CheckoutSessionSuperseded::class);
});

it('creates a fresh session via resolve-or-create when none is open', function () {
    $cart = makeCart();

    $session = app(CheckoutDriver::class)->resolveOrCreateSession($cart);

    expect($session)->toBeInstanceOf(CheckoutSession::class)
        ->and($session->status)->toBeInstanceOf(Open::class)
        ->and($session->active_cart_reference)->toBe((string) $cart->id);
});

it('refuses a create while a sibling is payment processing', function () {
    $cart = makeCart();

    CheckoutSession::factory()
        ->forCart($cart->id)
        ->paymentProcessing()
        ->create();

    app(CheckoutDriver::class)->createSession($cart);
})->throws(CheckoutSessionConflictException::class);

it('sets an expiry window on the session', function () {
    $cart = makeCart();

    $session = app(CheckoutDriver::class)->createSession($cart);

    expect($session->expires_at)->not->toBeNull()
        ->and($session->expires_at->isFuture())->toBeTrue()
        ->and($session->isExpired())->toBeFalse();
});

it('mints an unguessable uuid as the route key', function () {
    $cart = makeCart();

    $session = app(CheckoutDriver::class)->createSession($cart);

    expect($session->getRouteKeyName())->toBe('uuid')
        ->and($session->uuid)->not->toBe((string) $session->id);
});

it('rejects a non-lunar source cart', function () {
    makeCart();

    app(CheckoutDriver::class)->createSession(new stdClass);
})->throws(InvalidArgumentException::class);

it('expires expirable open sessions via the command', function () {
    $stale = CheckoutSession::factory()->expirable()->create();
    $fresh = CheckoutSession::factory()->create();

    $this->artisan('lunar:checkout:expire-sessions')->assertSuccessful();

    expect($stale->refresh()->status)->toBeInstanceOf(Expired::class)
        ->and($stale->active_cart_reference)->toBeNull()
        ->and($fresh->refresh()->status)->toBeInstanceOf(Open::class);
});

it('leaves a payment processing session alone when expiring', function () {
    $inFlight = CheckoutSession::factory()
        ->paymentProcessing()
        ->state(fn () => ['expires_at' => now()->subHour()])
        ->create();

    $this->artisan('lunar:checkout:expire-sessions')->assertSuccessful();

    expect($inFlight->refresh()->status->getValue())->toBe('payment-processing');
});
