<?php

namespace Lunar\Stripe\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Lunar\Stripe\Concerns\ConstructsWebhookEvent;
use Lunar\Stripe\Jobs\ProcessStripeWebhook;
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
            ]);
        }

        if (! in_array($event->type, ['payment_intent.succeeded', 'payment_intent.payment_failed'])) {
            return response()->json([
                'webhook_successful' => false,
                'message' => 'Unhandled event type '.$event->type,
            ], 400);
        }

        $paymentIntent = $event->data->object->id;
        $orderId = $event->data->object->metadata?->order_id;

        ProcessStripeWebhook::dispatch($paymentIntent, $orderId)->delay(now()->addMinute());

        return response()->json([
            'webhook_successful' => true,
            'message' => 'Webook handled successfully',
        ]);
    }
}
