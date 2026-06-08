<?php

use Lunar\Tests\Checkout\TestCase;

uses(TestCase::class);

it('renders the checkout page', function () {
    $this->get(route('lunar.checkout.show'))
        ->assertOk()
        ->assertSee('Checkout');
});

it('registers the named checkout route', function () {
    expect(route('lunar.checkout.show'))->toContain('/checkout');
});
