<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Contracts\Actions\Discounts\ResolvesCustomerEmail;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartAddress;
use Lunar\Tests\Core\Stubs\User;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

function cartWithContactEmails(?string $billing, ?string $shipping, ?User $user = null): Cart
{
    $cart = Cart::factory()->create(['user_id' => $user?->id]);

    CartAddress::factory()->create(['cart_id' => $cart->id, 'type' => 'billing', 'contact_email' => $billing]);
    CartAddress::factory()->create(['cart_id' => $cart->id, 'type' => 'shipping', 'contact_email' => $shipping]);

    return $cart->refresh();
}

test('prefers the signed-in user\'s email', function () {
    setAuthUserConfig();

    $user = User::factory()->create(['email' => 'user@example.com']);

    expect(app(ResolvesCustomerEmail::class)->execute(cartWithContactEmails('billing@example.com', 'shipping@example.com', $user)))
        ->toBe('user@example.com');
});

test('uses the billing email before the shipping email', function () {
    expect(app(ResolvesCustomerEmail::class)->execute(cartWithContactEmails('billing@example.com', 'shipping@example.com')))
        ->toBe('billing@example.com');
});

test('falls back to the shipping email', function () {
    expect(app(ResolvesCustomerEmail::class)->execute(cartWithContactEmails(null, 'shipping@example.com')))
        ->toBe('shipping@example.com');
});

test('lower-cases and trims the email', function () {
    expect(app(ResolvesCustomerEmail::class)->execute(cartWithContactEmails('  Billing@Example.COM ', null)))
        ->toBe('billing@example.com');
});

test('returns null when the cart has no email', function () {
    expect(app(ResolvesCustomerEmail::class)->execute(cartWithContactEmails(null, '  ')))->toBeNull()
        ->and(app(ResolvesCustomerEmail::class)->execute(Cart::factory()->create()))->toBeNull();
});
