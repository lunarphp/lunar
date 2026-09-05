<?php

namespace Lunar\Paypal\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Lunar\Paypal\Contracts\PaypalInterface;
use Lunar\Paypal\Jobs\ProcessPaypalWebhook;

class PaypalWebhookMiddleware
{
    public function __construct(
        protected PaypalInterface $paypal,
    ) {}

    public function handle(Request $request, ?Closure $next = null)
    {
        $payload = $request->json()->all();

        // PayPal verifies its own signatures — there is no local secret to HMAC
        // against, so this is a round trip to the API.
        $verified = $this->paypal->verifyWebhookSignature(
            array_map(fn (array $values): ?string => $values[0] ?? null, $request->headers->all()),
            $payload
        );

        if (! $verified) {
            abort(400, 'PayPal webhook signature could not be verified.');
        }

        if (! in_array($payload['event_type'] ?? null, ProcessPaypalWebhook::HANDLED_EVENTS, true)) {
            return response('', 200);
        }

        return $next($request);
    }
}
