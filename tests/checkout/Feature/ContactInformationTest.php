<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\Contracts\CheckoutDriver;
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

it('lets a signed-in customer view a session they own by customer_reference', function () {
    [$user, $customer] = makeUserWithCustomer();

    $ownerCart = routeTestCart();
    $session = app(CheckoutDriver::class)->createSession($ownerCart);
    $session->customer_reference = (string) $customer->id;
    $session->save();

    // A different (empty) cart is current, so cart-ownership would fail.
    CartSession::use(routeTestCart());
    $this->actingAs($user);

    $this->get(route('lunar.checkout.show', $session->uuid), ['X-Inertia' => 'true'])
        ->assertOk();
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
