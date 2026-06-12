<?php

use Lunar\Tests\Checkout\TestCase;

uses(TestCase::class);

it('renders the checkout page', function () {
    $this->get(route('lunar.checkout.show'))
        ->assertOk()
        ->assertSee('Checkout');
})->skip(
    fn (): bool => ! is_file(public_path('vendor/lunarphp/checkout/build/manifest.json')),
    'Requires the published checkout app build (vendor:publish --tag=lunar-checkout-assets).',
);

it('registers the named checkout route', function () {
    expect(route('lunar.checkout.show'))->toContain('/checkout');
});
