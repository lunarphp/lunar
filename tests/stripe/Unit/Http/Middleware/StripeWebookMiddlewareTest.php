<?php

use Illuminate\Http\Request;
use Lunar\Stripe\Concerns\ConstructsWebhookEvent;
use Lunar\Stripe\Http\Middleware\StripeWebhookMiddleware;
use Lunar\Tests\Stripe\Unit\TestCase;
use Stripe\Event;

uses(TestCase::class)->group('lunar.stripe.middleware');

it('can handle valid event', function () {
    $this->app->bind(ConstructsWebhookEvent::class, function ($app) {
        return new class implements ConstructsWebhookEvent
        {
            public function constructEvent(string $jsonPayload, string $signature, string $secret)
            {
                return Event::constructFrom([
                    'type' => 'payment_intent.succeeded',
                ]);
            }
        };
    });

    $request = Request::create('/strip-webhook', 'POST');
    $request->headers->set('Stripe-Signature', 'foobar');
    $middleware = new StripeWebhookMiddleware([]);

    $request = $middleware->handle($request, fn ($request) => $request);

    expect($request)->toBeInstanceOf(Request::class);
});
