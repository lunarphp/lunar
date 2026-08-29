<?php

use Illuminate\Support\Facades\Event;
use Lunar\Stripe\Events\Webhook\CartMissingForIntent;
use Lunar\Stripe\Jobs\ProcessStripeWebhook;
use Lunar\Tests\Stripe\Unit\TestCase;

uses(TestCase::class)->group('lunar.stripe.jobs');

it('will dispatch event if payment intent has no found cart or order', function () {
    Event::fake();

    ProcessStripeWebhook::dispatchSync('PI_FOOBAR', null);

    Event::assertDispatched(
        CartMissingForIntent::class,
    );
});

it('will dispatch event if the order id in the intent metadata no longer exists', function () {
    Event::fake();

    ProcessStripeWebhook::dispatchSync('PI_FOOBAR', '999999');

    Event::assertDispatched(
        CartMissingForIntent::class,
    );
});
