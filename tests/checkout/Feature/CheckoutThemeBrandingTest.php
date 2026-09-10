<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\DataObjects\CheckoutTheme;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;

uses(TestCase::class, RefreshDatabase::class);

/**
 * Spec 0008 §D. The checkout is its own document, so the store's tab icon and
 * name have to be handed to it through the theme.
 */
it('renders the theme favicon and the merchant name in the root view head', function () {
    app()->bind(CheckoutTheme::class, fn (): CheckoutTheme => CheckoutTheme::tender()->with(favicon: '/favicon.svg'));
    config()->set('checkout.merchant', 'Edwardes Bros');

    $cart = CheckoutCart::orderable();
    $session = CheckoutCart::session($cart);

    $this->withoutVite()->get(route('lunar.checkout.show', $session->uuid))
        ->assertOk()
        ->assertSee('<link rel="icon" href="/favicon.svg">', false)
        ->assertSee('<title>Checkout · Edwardes Bros</title>', false);
});

it('renders no icon link when the theme sets none', function () {
    $cart = CheckoutCart::orderable();
    $session = CheckoutCart::session($cart);

    $this->withoutVite()->get(route('lunar.checkout.show', $session->uuid))
        ->assertOk()
        ->assertDontSee('rel="icon"', false);
});

it('refuses an unsafe favicon url', function () {
    expect(fn () => CheckoutTheme::tender()->with(favicon: 'javascript:alert(1)')->favicon())
        ->toThrow(InvalidArgumentException::class);
});

it('emits the pinnable accent tints as tokens', function () {
    $tokens = CheckoutTheme::tender()->with(
        accent: '#2d8e42',
        accentSoft: '#ecf7ee',
        accentSoftBorder: '#d3ecd9',
        accentRing: 'rgba(45, 142, 66, 0.16)',
    )->tokens();

    expect($tokens)->toBe([
        '--accent' => '#2d8e42',
        '--accent-soft' => '#ecf7ee',
        '--accent-soft-border' => '#d3ecd9',
        '--accent-ring' => 'rgba(45, 142, 66, 0.16)',
    ]);
});
