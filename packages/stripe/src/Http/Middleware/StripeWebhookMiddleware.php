<?php

namespace Lunar\Stripe\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Lunar\Stripe\Concerns\ConstructsWebhookEvent;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Exception\UnexpectedValueException;

class StripeWebhookMiddleware
{
    public function handle(Request $request, ?Closure $next = null)
    {
        $secret = config('services.stripe.webhooks.lunar');
        $stripeSig = $request->header('Stripe-Signature');

        try {
            $event = app(ConstructsWebhookEvent::class)->constructEvent(
                $request->getContent(),
                $stripeSig,
                $secret
            );
        } catch (UnexpectedValueException|SignatureVerificationException $e) {
            abort(400, $e->getMessage());
        }

        if (! in_array(
            $event->type,
            [
                'payment_intent.payment_failed',
                'payment_intent.succeeded',
            ]
        )) {
            return response('', 200);
        }

        return $next($request);
    }
}
