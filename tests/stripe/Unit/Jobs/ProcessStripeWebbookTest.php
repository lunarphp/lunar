<?php

use Lunar\Stripe\Jobs\ProcessStripeWebhook;

uses(\Lunar\Tests\Stripe\Unit\TestCase::class)->group('lunar.stripe.jobs');

it('will dispatch event if payment intent has no found cart or order', function () {
    \Illuminate\Support\Facades\Event::fake();

    ProcessStripeWebhook::dispatch('PI_FOOBAR', null);

    \Illuminate\Support\Facades\Event::assertDispatched(
        \Lunar\Stripe\Events\Webhook\CartMissingForIntent::class,
    );
});
