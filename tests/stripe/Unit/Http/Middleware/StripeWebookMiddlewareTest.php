<?php

uses(\Lunar\Tests\Stripe\Unit\TestCase::class)->group('lunar.stripe.middleware');

it('can handle valid event', function () {
    $this->app->bind(\Lunar\Stripe\Concerns\ConstructsWebhookEvent::class, function ($app) {
        return new class implements \Lunar\Stripe\Concerns\ConstructsWebhookEvent
        {
            public function constructEvent(string $jsonPayload, string $signature, string $secret)
            {
                return \Stripe\Event::constructFrom([]);
            }
        };
    });

    $request = \Illuminate\Http\Request::create('/strip-webhook', 'POST');
    $request->headers->set('Stripe-Signature', 'foobar');
    $middleware = new \Lunar\Stripe\Http\Middleware\StripeWebhookMiddleware([]);

    $request = $middleware->handle($request, fn ($request) => $request);

    expect($request->status())->toBe(200);
});
