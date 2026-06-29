<?php

namespace Lunar\Stripe\Actions;

use Illuminate\Support\Facades\DB;
use Lunar\Core\Models\Order;
use Lunar\Stripe\Facades\Stripe;
use Stripe\PaymentIntent;

class UpdateOrderFromIntent
{
    public static function execute(
        Order $order,
        PaymentIntent $paymentIntent
    ): Order {
        return DB::transaction(function () use ($order, $paymentIntent) {

            $charges = Stripe::getCharges($paymentIntent->id);

            $order = app(StoreCharges::class)->store($order, $charges);
            $requiresCapture = $paymentIntent->status === PaymentIntent::STATUS_REQUIRES_CAPTURE;

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
                'placed_at' => $order->placed_at ?: $placedAt,
            ]);

            return $order;
        });
    }
}
