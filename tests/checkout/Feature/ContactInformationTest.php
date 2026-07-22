<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Checkout\Contracts\ElementRegistry;
use Lunar\Checkout\Elements\ContactInformation;
use Lunar\Checkout\Session\CheckoutSession as SessionBag;
use Lunar\Core\Facades\CartSession;
use Lunar\Core\Models\Customer;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Core\Stubs\User;

uses(TestCase::class, RefreshDatabase::class);

/** @return array{0: User, 1: Customer} */
function makeUserWithCustomer(): array
{
    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    $user->customers()->attach($customer);

    return [$user, $customer];
}

it('describes itself as the contact region element', function () {
    $element = (new ContactInformation)->setSession(new SessionBag(app('session.store')));

    expect($element->handle())->toBe('contact')
        ->and($element->component())->toBe('contact-information')
        ->and($element->region())->toBe('contact');
});

it('projects guest contact state when not authenticated', function () {
    $element = (new ContactInformation)->setSession(new SessionBag(app('session.store')));
    $props = $element->props();

    expect($props['signedIn'])->toBeFalse()
        ->and($props['email'])->toBeNull()
        ->and($props['passkeysEnabled'])->toBeFalse();
});

it('owns a session by customer_reference when the cart differs, and reconciles instead of forbidding', function () {
    [$user, $customer] = makeUserWithCustomer();

    $ownerCart = routeTestCart();
    $session = app(CheckoutDriver::class)->createSession($ownerCart);
    $session->customer_reference = (string) $customer->id;
    $session->save();

    // A different (empty) cart is current, so cart-ownership would fail
    // without the customer_reference fallback — ensureOwnership lets this
    // through (not a 403). show() then treats the mismatched cart as a swap
    // (see the cart-swap reconcile test below) and hands off to the fresh
    // session for the current cart rather than rendering the stale one.
    CartSession::use(routeTestCart());
    $this->actingAs($user);

    $this->get(route('lunar.checkout.show', $session->uuid), ['X-Inertia' => 'true'])
        ->assertConflict();
});

it('forbids a signed-in customer from viewing another customer\'s session', function () {
    [$ownerUser, $ownerCustomer] = makeUserWithCustomer();
    $ownerCart = routeTestCart();
    $session = app(CheckoutDriver::class)->createSession($ownerCart);
    $session->customer_reference = (string) $ownerCustomer->id;
    $session->save();

    // A DIFFERENT signed-in customer, with their own (different) current cart.
    [$otherUser, $otherCustomer] = makeUserWithCustomer();
    CartSession::use(routeTestCart());
    $this->actingAs($otherUser);

    $this->get(route('lunar.checkout.show', $session->uuid))->assertForbidden();
});

it('reports whether an email has an account, for the session owner', function () {
    [$user] = makeUserWithCustomer();
    $user->update(['email' => 'known@example.test']);

    $cart = routeTestCart();
    $session = app(CheckoutDriver::class)->createSession($cart);
    CartSession::use($cart);

    $this->postJson(route('lunar.checkout.contact.lookup', $session->uuid), ['email' => 'known@example.test'])
        ->assertOk()->assertExactJson(['exists' => true]);

    $this->postJson(route('lunar.checkout.contact.lookup', $session->uuid), ['email' => 'nobody@example.test'])
        ->assertOk()->assertExactJson(['exists' => false]);
});

it('forbids lookup on a session the requester does not own', function () {
    $ownCart = routeTestCart();
    $session = app(CheckoutDriver::class)->createSession($ownCart);
    $otherCart = routeTestCart();
    CartSession::use($otherCart); // different cart, guest

    $this->postJson(route('lunar.checkout.contact.lookup', $session->uuid), ['email' => 'x@example.test'])
        ->assertForbidden();
});

it('stores a guest email onto the checkout session model', function () {
    $cart = routeTestCart();
    $session = app(CheckoutDriver::class)->createSession($cart);
    CartSession::use($cart);

    $this->post(route('lunar.checkout.contact.store', $session->uuid), ['email' => 'guest@example.test'])
        ->assertRedirect();

    expect($session->fresh()->customer_email)->toBe('guest@example.test')
        ->and($session->fresh()->customer_reference)->toBeNull();
});

it('associates the customer when authenticated', function () {
    [$user, $customer] = makeUserWithCustomer();
    $user->update(['email' => 'trade@example.test']);

    $cart = routeTestCart();
    $session = app(CheckoutDriver::class)->createSession($cart);
    CartSession::use($cart);
    $this->actingAs($user);

    $this->post(route('lunar.checkout.contact.store', $session->uuid), ['email' => 'trade@example.test'])
        ->assertRedirect();

    expect($session->fresh()->customer_reference)->toBe((string) $customer->id)
        ->and($session->fresh()->customer_email)->toBe('trade@example.test');
});

it('projects the persisted guest email into the contact element', function () {
    app(ElementRegistry::class)->add(ContactInformation::class);

    $cart = routeTestCart();
    $session = app(CheckoutDriver::class)->createSession($cart);
    $session->customer_email = 'guest@example.test';
    $session->save();
    CartSession::use($cart);

    $this->get(route('lunar.checkout.show', $session->uuid), ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('props.checkout.elements.0.props.email', 'guest@example.test');
});

it('projects a login url when the host names a login route', function () {
    Route::get('login-test-stub', fn () => '')->name('login');
    app(ElementRegistry::class)->add(ContactInformation::class);

    $cart = routeTestCart();
    $session = app(CheckoutDriver::class)->createSession($cart);
    CartSession::use($cart);

    $this->get(route('lunar.checkout.show', $session->uuid), ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('props.checkout.elements.0.props.loginUrl', route('login'));
});

it('projects a null login url when the host has no login route', function () {
    app(ElementRegistry::class)->add(ContactInformation::class);

    $cart = routeTestCart();
    $session = app(CheckoutDriver::class)->createSession($cart);
    CartSession::use($cart);

    $this->get(route('lunar.checkout.show', $session->uuid), ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('props.checkout.elements.0.props.loginUrl', null);
});

it('injects contact urls into the projected contact element', function () {
    app(ElementRegistry::class)->add(ContactInformation::class);

    $cart = routeTestCart();
    $session = app(CheckoutDriver::class)->createSession($cart);
    CartSession::use($cart);

    $this->get(route('lunar.checkout.show', $session->uuid), ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('props.checkout.elements.0.handle', 'contact')
        ->assertJsonPath('props.checkout.elements.0.props.lookupUrl', route('lunar.checkout.contact.lookup', $session->uuid))
        ->assertJsonPath('props.checkout.elements.0.props.contactUrl', route('lunar.checkout.contact.store', $session->uuid));
});

it('re-resolves to the current cart session when the cart was swapped on login', function () {
    [$user, $customer] = makeUserWithCustomer();

    // Stale session pinned to an old cart the user now owns by customer_reference.
    $oldCart = routeTestCart();
    $stale = app(CheckoutDriver::class)->createSession($oldCart);
    $stale->customer_reference = (string) $customer->id;
    $stale->save();

    // A different cart is now current (simulating the post-login merge).
    $currentCart = routeTestCart();
    CartSession::use($currentCart);
    $this->actingAs($user);

    $response = $this->get(route('lunar.checkout.show', $stale->uuid));
    // Inertia::location → 409 for Inertia callers, 302 otherwise; here a plain GET → 302.
    $response->assertRedirect();
    expect($response->headers->get('Location'))->not->toContain($stale->uuid);
});
