<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Core\Contracts\StorefrontSession;
use Lunar\Core\Facades\CartSession;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Tests\Checkout\TestCase;

uses(TestCase::class, RefreshDatabase::class);

/**
 * Storefront context every request has in a real app (default channel +
 * currency), without a cart — so CartSession::current() can resolve and
 * return null rather than throw.
 *
 * @return array{0: Channel, 1: Currency}
 */
function routeCheckoutContext(): array
{
    // Idempotent: callable more than once per test (e.g. two carts) without
    // colliding on the unique channel handle / currency code.
    if (! CustomerGroup::query()->where('default', true)->exists()) {
        CustomerGroup::factory()->create(['default' => true]);
    }

    $channel = Channel::query()->firstWhere('handle', 'webstore')
        ?? Channel::factory()->create(['handle' => 'webstore']);
    $currency = Currency::query()->firstWhere('code', 'GBP')
        ?? Currency::factory()->create(['code' => 'GBP']);

    app(StorefrontSession::class)
        ->setChannel($channel)
        ->setCurrency($currency);

    return [$channel, $currency];
}

function routeTestCart(): Cart
{
    [$channel, $currency] = routeCheckoutContext();

    return Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
    ]);
}

it('registers the start and show routes under /checkout', function () {
    expect(route('lunar.checkout.start', absolute: false))->toBe('/checkout')
        ->and(route('lunar.checkout.show', 'abc-123', absolute: false))->toBe('/checkout/abc-123');
});

it('starts a checkout and redirects to the session UUID url', function () {
    $cart = routeTestCart();
    CartSession::use($cart);

    $session = null;

    $this->post(route('lunar.checkout.start'))
        ->assertRedirect();

    $session = CheckoutSession::query()->firstOrFail();

    expect($session->cart_reference)->toBe((string) $cart->id);

    $this->post(route('lunar.checkout.start'))
        ->assertRedirect(route('lunar.checkout.show', $session->uuid));

    // Resolve-or-create: re-pressing checkout resumes the SAME session.
    expect(CheckoutSession::query()->count())->toBe(1);
});

it('redirects back without a session when there is no cart', function () {
    routeCheckoutContext();

    $this->post(route('lunar.checkout.start'))
        ->assertRedirect();

    expect(CheckoutSession::query()->count())->toBe(0);
});

it('forbids viewing a session the requester does not own', function () {
    $ownerCart = routeTestCart();
    $session = app(CheckoutDriver::class)->createSession($ownerCart);

    $otherCart = routeTestCart();
    CartSession::use($otherCart);

    $this->get(route('lunar.checkout.show', $session->uuid))
        ->assertForbidden();
});

it('projects the configured merchant name into the header prop', function () {
    config(['checkout.merchant' => 'Edwardes Bros']);

    $cart = routeTestCart();
    $session = app(CheckoutDriver::class)->createSession($cart);
    CartSession::use($cart);

    // The X-Inertia header makes show() return the prop payload as JSON, so the
    // merchant projection is asserted without needing the built app manifest.
    $this->get(route('lunar.checkout.show', $session->uuid), ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('component', 'Show')
        ->assertJsonPath('props.checkout.merchant', 'Edwardes Bros');
});

it('falls back to the app name when no merchant is configured', function () {
    config(['checkout.merchant' => null, 'app.name' => 'Lunar Store']);

    $cart = routeTestCart();
    $session = app(CheckoutDriver::class)->createSession($cart);
    CartSession::use($cart);

    $this->get(route('lunar.checkout.show', $session->uuid), ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('props.checkout.merchant', 'Lunar Store');
});

it('404s an unknown session uuid', function () {
    routeTestCart();

    $this->get(route('lunar.checkout.show', 'does-not-exist'))
        ->assertNotFound();
});

it('renders the checkout for the owning cart', function () {
    $cart = routeTestCart();
    $session = app(CheckoutDriver::class)->createSession($cart);
    CartSession::use($cart);

    $this->get(route('lunar.checkout.show', $session->uuid))
        ->assertOk()
        ->assertSee('Checkout');
})->skip(
    fn (): bool => ! is_file(public_path('vendor/lunarphp/checkout/build/manifest.json')),
    'Requires the published checkout app build (vendor:publish --tag=lunar-checkout-assets).',
);
