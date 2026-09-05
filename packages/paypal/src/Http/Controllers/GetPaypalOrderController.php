<?php

namespace Lunar\Paypal\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Lunar\Core\Contracts\CartSession;
use Lunar\Paypal\Contracts\PaypalInterface;

/**
 * Creates the PayPal order the customer approves, for the current session cart.
 *
 * Returns only what the client needs to hand to the PayPal JS SDK — creating a
 * PayPal order costs an API call, so the route is rate limited, and the raw
 * response carries payer details and internal links a storefront has no use for.
 */
class GetPaypalOrderController extends Controller
{
    public function __construct(
        protected CartSession $cartSession,
        protected PaypalInterface $paypal,
    ) {}

    public function __invoke(): JsonResponse
    {
        $cart = $this->cartSession->current();

        if (! $cart || $cart->lines->isEmpty()) {
            return response()->json([
                'message' => 'No cart to pay for.',
            ], 422);
        }

        $order = $this->paypal->buildInitialOrder($cart);

        if (! $id = ($order['id'] ?? null)) {
            return response()->json([
                'message' => 'Unable to create a PayPal order.',
            ], 502);
        }

        return response()->json([
            'id' => $id,
            'status' => $order['status'] ?? null,
            'approve_url' => collect($order['links'] ?? [])
                ->firstWhere('rel', 'approve')['href'] ?? null,
        ]);
    }
}
