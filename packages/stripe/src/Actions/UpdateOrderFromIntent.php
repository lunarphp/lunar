<?php

namespace Lunar\Stripe\Actions;

use Illuminate\Support\Facades\DB;
use Lunar\Models\Order;
use Lunar\Stripe\Facades\Stripe;
use Stripe\PaymentIntent;

class UpdateOrderFromIntent
{
    final public static function execute(
        Order $order,
        PaymentIntent $paymentIntent,
        string $successStatus = 'paid',
        string $failStatus = 'failed'
    ): Order {
        return DB::transaction(function () use ($order, $paymentIntent) {

            $charges = Stripe::getCharges($paymentIntent->id);

            $order = app(StoreCharges::class)->store($order, $charges);
            $requiresCapture = $paymentIntent->status === PaymentIntent::STATUS_REQUIRES_CAPTURE;

            $statuses = config('lunar.stripe.status_mapping', []);

            $placedAt = null;

            $billingAddress = $order->billingAddress;

            $paymentMethod = Stripe::getPaymentMethod($paymentIntent->payment_method);

            $billingDetails = $paymentMethod->billing_details;
            $postcode = $billingDetails->address->postal_code;

            if ($postcode != $billingAddress->postcode) {
                dd($billingDetails);
            }

            if ($paymentIntent->status === PaymentIntent::STATUS_SUCCEEDED) {
                $placedAt = now();
            }

            if ($charges->isEmpty() && ! $requiresCapture) {
                return $order;
            }

            $order->update([
                'status' => $statuses[$paymentIntent->status] ?? $paymentIntent->status,
                'placed_at' => $order->placed_at ?: $placedAt,
            ]);

            return $order;
        });
    }
}
