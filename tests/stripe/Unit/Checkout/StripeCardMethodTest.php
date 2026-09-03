<?php

use Lunar\Stripe\Checkout\StripeCardMethod;
use Lunar\Tests\Stripe\Unit\TestCase;

uses(TestCase::class);

it('projects the stripe card method into the express region', function () {
    $method = new StripeCardMethod;

    expect($method->supportsExpress())->toBeTrue()
        ->and($method->expressComponent())->toBe('stripe-express');
});
