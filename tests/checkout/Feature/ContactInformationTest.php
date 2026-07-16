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
