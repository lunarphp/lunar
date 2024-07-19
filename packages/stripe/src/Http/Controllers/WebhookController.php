<?php

namespace Lunar\Stripe\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Events\PaymentAttemptEvent;
use Lunar\Facades\Payments;
use Lunar\Models\Cart;
use Lunar\Stripe\Concerns\ConstructsWebhookEvent;
use Lunar\Stripe\Facades\Stripe;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Exception\UnexpectedValueException;

final class WebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $secret = config('services.stripe.webhooks.payment_intent');
        $stripeSig = $request->header('Stripe-Signature');

        try {
            $event = app(ConstructsWebhookEvent::class)->constructEvent(
                $request->getContent(),
                $stripeSig,
                $secret
            );
        } catch (UnexpectedValueException|SignatureVerificationException $e) {
            Log::error(
                $error = $e->getMessage()
            );

            return response()->json([
                'webhook_successful' => false,
                'message' => $error,
            ], 400);
        }

        $paymentIntent = $event->data->object->id;

        $cart = Cart::where('meta->payment_intent', '=', $paymentIntent)->first();

        if (! $cart) {
            Log::error(
                $error = "Unable to find cart with intent {$paymentIntent}"
            );

            return response()->json([
                'webhook_successful' => false,
                'message' => $error,
            ], 400);
        }

        if ($cart->recalculate()->total->value != $event->data->object->amount) {
            Stripe::refundCharge($event->data->object->latest_charge);

            Stripe::updateIntent($cart, [
                'description' => 'Cart value mismatch',
            ]);

            PaymentAttemptEvent::dispatch(
                new PaymentAuthorize(
                    success: false,
                    message: 'Cart value mismatch',
                    orderId: null,
                    paymentType: 'stripe'
                )
            );

            return response()->json([
                'webhook_successful' => false,
                'message' => 'Charge refunded due to value mismatch',
            ]);
        }

        $payment = Payments::driver('stripe')->cart($cart->calculate())->withData([
            'payment_intent' => $paymentIntent,
        ])->authorize();

        PaymentAttemptEvent::dispatch($payment);

        return response()->json([
            'webhook_successful' => true,
            'message' => 'Webook handled successfully',
        ]);
    }
}
