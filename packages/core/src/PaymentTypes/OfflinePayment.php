<?php

namespace Lunar\Core\PaymentTypes;

use Illuminate\Support\Str;
use Lunar\Core\DataObjects\PaymentAuthorize;
use Lunar\Core\DataObjects\PaymentCapture;
use Lunar\Core\DataObjects\PaymentRefund;
use Lunar\Core\Events\PaymentAttemptEvent;
use Lunar\Core\Models\Transaction;

class OfflinePayment extends AbstractPayment
{
    /**
     * {@inheritDoc}
     */
    public function authorize(): ?PaymentAuthorize
    {
        if (! $this->order) {
            if (! $this->order = $this->cart->draftOrder()->first()) {
                $this->order = $this->cart->createOrder();
            }
        }
        $orderMeta = array_merge(
            (array) $this->order->meta,
            $this->data['meta'] ?? []
        );

        $this->order->update([
            'meta' => $orderMeta,
            'placed_at' => now(),
        ]);

        $response = new PaymentAuthorize(
            success: true,
            orderId: $this->order->id,
            paymentType: 'offline',
        );

        PaymentAttemptEvent::dispatch($response);

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function refund(Transaction $transaction, int $amount = 0, $notes = null): PaymentRefund
    {
        $refundTransaction = $transaction->order->transactions()->create([
            'success' => true,
            'type' => 'refund',
            'driver' => 'offline',
            'amount' => $amount,
            'reference' => (string) Str::uuid(),
            'status' => 'refunded',
            'notes' => $notes,
            'card_type' => $transaction->card_type,
            'last_four' => $transaction->last_four,
        ]);

        return new PaymentRefund(success: true, transaction: $refundTransaction);
    }

    /**
     * {@inheritDoc}
     */
    public function capture(Transaction $transaction, $amount = 0): PaymentCapture
    {
        return new PaymentCapture(true);
    }
}
