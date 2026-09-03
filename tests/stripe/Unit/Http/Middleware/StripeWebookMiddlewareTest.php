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

/**
 * A hold's authorisation event (`amount_capturable_updated` fires when a
 * manual-capture intent reaches `requires_capture`) must never reach the
 * webhook job: only `payment_intent.payment_failed` and
 * `payment_intent.succeeded` pass this gate, so an authorisation is always a
 * no-op response, never a session completion or payment-success read.
 */
it('never forwards amount_capturable_updated to the next handler', function () {
    $this->app->bind(ConstructsWebhookEvent::class, function ($app) {
        return new class implements ConstructsWebhookEvent
        {
            public function constructEvent(string $jsonPayload, string $signature, string $secret)
            {
                return Event::constructFrom([
                    'type' => 'payment_intent.amount_capturable_updated',
                ]);
            }
        };
    });

    $request = Request::create('/strip-webhook', 'POST');
    $request->headers->set('Stripe-Signature', 'foobar');
    $middleware = new StripeWebhookMiddleware([]);

    $reachedNext = false;

    $response = $middleware->handle($request, function ($request) use (&$reachedNext) {
        $reachedNext = true;

        return $request;
    });

    expect($reachedNext)->toBeFalse()
        ->and($response->getStatusCode())->toBe(200);
});
