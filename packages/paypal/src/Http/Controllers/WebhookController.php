<?php

namespace Lunar\Paypal\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Lunar\Paypal\Jobs\ProcessPaypalWebhook;
use Lunar\Paypal\Models\PaypalOrder;

class WebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->json()->all();

        $paypalOrderId = ProcessPaypalWebhook::resolvePaypalOrderId($payload);

        // Don't queue work behind an authorize() that is already in flight —
        // the driver will finish the job itself.
        $paypalOrder = $paypalOrderId
            ? PaypalOrder::where('paypal_order_id', $paypalOrderId)->first()
            : null;

        if ($paypalOrder?->processing_at && ! $paypalOrder->processed_at) {
            return response()->json([
                'webhook_successful' => true,
                'message' => 'Already being processed',
            ]);
        }

        $paypalOrder?->update(['event_id' => $payload['id'] ?? null]);

        ProcessPaypalWebhook::dispatch($payload)->delay(now()->addSeconds(5));

        return response()->json([
            'webhook_successful' => true,
            'message' => 'Webhook handled successfully',
        ]);
    }
}
