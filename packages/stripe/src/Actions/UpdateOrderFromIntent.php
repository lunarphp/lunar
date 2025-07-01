<?php

namespace Lunar\Stripe\Actions;

use Illuminate\Support\Facades\DB;
use Lunar\Models\Contracts\Order as OrderContract;
use Lunar\Stripe\Facades\Stripe;
use Stripe\PaymentIntent;

class UpdateOrderFromIntent
{
    final public static function execute(
        OrderContract $order,
        PaymentIntent $paymentIntent,
        string $successStatus = 'paid',
        string $failStatus = 'failed'
    ): OrderContract {
        return DB::transaction(function () use ($order, $paymentIntent) {

            $charges = Stripe::getCharges($paymentIntent->id);

            $order = app(StoreCharges::class)->store($order, $charges);
            $requiresCapture = $paymentIntent->status === PaymentIntent::STATUS_REQUIRES_CAPTURE;

            $statuses = config('lunar.stripe.status_mapping', []);

            $placedAt = null;

            if ($paymentIntent->status === PaymentIntent::STATUS_SUCCEEDED) {
                $placedAt = now();
            }

            if ($charges->isEmpty() && ! $requiresCapture) {
                return $order;
            }

            if (config('lunar.stripe.sync_addresses', true) && $paymentIntent->payment_method) {
                (new StoreAddressInformation)->store($order, $paymentIntent);
            }

            $order->update([
                'status' => $statuses[$paymentIntent->status] ?? $paymentIntent->status,
                'placed_at' => $order->placed_at ?: $placedAt,
            ]);

            return $order;
        });
    }
}
