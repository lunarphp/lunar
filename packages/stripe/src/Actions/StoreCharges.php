<?php

namespace Lunar\Stripe\Actions;

use Illuminate\Support\Collection;
use Lunar\Models\Contracts\Order as OrderContract;
use Lunar\Models\Order;
use Lunar\Models\Transaction;

class StoreCharges
{
    public function store(OrderContract $order, Collection $charges)
    {
        /** @var Order $order */
        /**
         * If charges are empty, there is nothing to update.
         */
        if ($charges->isEmpty()) {
            return $order;
        }

        /**
         * Get the most up to date transactions.
         */
        $transactions = $order->transactions()->get();

        foreach ($charges as $charge) {
            $timestamp = now()->createFromTimestamp($charge->created);

            $transaction = $transactions->first(
                fn ($t) => $t->reference == $charge->id
            ) ?: new Transaction;

            $type = 'capture';

            if (! $charge->captured) {
                $type = 'intent';
            }

            if ($charge->amount_refunded && $charge->amount_refunded < $charge->amount) {
                $type = 'refund';
            }

            $paymentType = collect($charge->payment_method_details)->keys()->first();
            $paymentDetails = collect($charge->payment_method_details)->first();

            $lastFour = null;
            $cardType = $paymentType;
            $meta = [];

            if (! empty($paymentDetails['brand'])) {
                $cardType = $paymentDetails['brand'];
            }

            if (! empty($paymentDetails['last4'])) {
                $lastFour = $paymentDetails['last4'];
            }

            if (! empty($paymentDetails['checks'])) {
                $meta = array_merge($meta, (array) $paymentDetails['checks']);
            }

            $transaction->fill([
                'order_id' => $order->id,
                'success' => (bool) ! $charge->failure_code,
                'type' => $charge->refunded ? 'refund' : $type,
                'driver' => 'stripe',
                'amount' => $charge->amount,
                'reference' => $charge->id,
                'status' => $charge->status,
                'notes' => $charge->failure_message ?: $charge->description,
                'card_type' => $cardType,
                'last_four' => $lastFour,
                'captured_at' => $charge->amount_captured ? $timestamp : null,
                'meta' => $meta,
            ]);

            $transaction->save();
        }

        return $order;
    }
}
