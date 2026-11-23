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
            // Set on the cart first so order creation validates the terms that win.
            if ($terms = $this->terms()) {
                $this->cart->setPaymentTerms($terms);
            }

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
            'payment_terms' => $this->terms() ?? $this->order->payment_terms,
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
     * Record the capture of an offline authorization. There is no gateway to
     * call, so capturing means the store has taken the money: write the
     * capture transaction against the intent, the way {@see refund()} writes
     * the refund transaction, so the ledger and the order's payment status
     * reflect it. An amount of zero captures the whole authorization.
     */
    public function capture(Transaction $transaction, $amount = 0): PaymentCapture
    {
        $transaction->order->transactions()->create([
            'success' => true,
            'type' => 'capture',
            'driver' => 'offline',
            'amount' => $amount > 0 ? $amount : $transaction->amount,
            'reference' => (string) Str::uuid(),
            'status' => 'settled',
            'parent_transaction_id' => $transaction->id,
            'card_type' => $transaction->card_type,
            'last_four' => $transaction->last_four,
            'captured_at' => now(),
        ]);

        return new PaymentCapture(true);
    }
}
