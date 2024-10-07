<?php

namespace Lunar\Paypal;

use Illuminate\Http\Client\HttpClientException;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Events\PaymentAttemptEvent;
use Lunar\Models\Contracts\Transaction as TransactionContract;
use Lunar\Models\Transaction;
use Lunar\PaymentTypes\AbstractPayment;
use Lunar\Paypal\Facades\Paypal;

class PaypalPaymentType extends AbstractPayment
{
    /**
     * The policy when capturing payments.
     *
     * @var string
     */
    protected $policy;

    /**
     * Initialise the payment type.
     */
    public function __construct()
    {
        $this->policy = config('lunar.paypal.policy', 'automatic');
    }

    /**
     * Authorize the payment for processing.
     */
    public function authorize(): PaymentAuthorize
    {
        if (! $this->order) {
            if (! $this->order = $this->cart->order) {
                $this->order = $this->cart->createOrder();
            }
        }

        if ($this->order->placed_at) {
            // Somethings gone wrong!
            $failure = new PaymentAuthorize(
                success: false,
                message: 'This order has already been placed',
                orderId: $this->order->id,
                paymentType: 'paypal',
            );

            PaymentAttemptEvent::dispatch($failure);

            return $failure;
        }

        $paypalOrder = Paypal::getOrder(
            $paypalOrderId = $this->data['paypal_order_id']
        );

        if (isset($paypalOrder['name']) && $paypalOrder['name'] == 'RESOURCE_NOT_FOUND') {
            $failedResponse = new PaymentAuthorize(
                success: false,
                orderId: $this->order?->id,
                paymentType: 'paypal',
            );

            PaymentAttemptEvent::dispatch($failedResponse);

            return $failedResponse;
        }

        if ($paypalOrder['status'] == 'APPROVED') {
            $paypalOrder = PayPal::capture($paypalOrderId);

            if (($paypalOrder['status'] ?? null) != 'COMPLETED') {
                return $this->failAuthorize();
            }
        }

        $transactions = collect();

        // Build out the transactions.
        foreach ($paypalOrder['purchase_units'] as $purchaseUnit) {
            foreach ($purchaseUnit['payments']['captures'] ?? [] as $capture) {
                $transactions->push(
                    [
                        'success' => $capture['status'] == 'COMPLETED',
                        'type' => 'capture',
                        'driver' => 'paypal',
                        'amount' => (int) ($capture['amount']['value'] * 100),
                        'reference' => $capture['id'],
                        'status' => $capture['status'],
                        'card_type' => 'paypal',
                        'captured_at' => now()->parse($capture['create_time']),
                    ]
                );
            }
        }

        $this->order->transactions()->createMany($transactions);

        $status = $this->data['status'] ?? null;

        $this->order->update([
            'status' => $status ?? ($this->config['authorized'] ?? null),
            'placed_at' => now(),
        ]);

        $response = new PaymentAuthorize(
            success: true,
            orderId: $this->order->id,
            paymentType: 'paypal',
        );

        PaymentAttemptEvent::dispatch($response);

        return $response;
    }

    private function failAuthorize()
    {
        $response = new PaymentAuthorize(
            success: false,
            orderId: $this->order?->id,
            paymentType: 'paypal',
        );

        PaymentAttemptEvent::dispatch($response);

        return $response;
    }

    /**
     * Capture a payment for a transaction.
     *
     * @param  int  $amount
     */
    public function capture(TransactionContract $transaction, $amount = 0): PaymentCapture
    {
        return new PaymentCapture(success: true);
    }

    /**
     * Refund a captured transaction
     *
     * @param  string|null  $notes
     */
    public function refund(TransactionContract $transaction, int $amount = 0, $notes = null): PaymentRefund
    {

        $currencyCode = $transaction->order->currency_code;

        try {
            $response = Paypal::refund(
                $transaction->reference,
                (string) ($amount / 100),
                $currencyCode
            );

            $transaction->order->transactions()->create([
                'success' => true,
                'type' => 'refund',
                'driver' => 'paypal',
                'amount' => $amount,
                'reference' => $response['id'] ?? $transaction->reference,
                'status' => $response['status'] ?? 'COMPLETED',
                'notes' => $notes,
                'card_type' => $transaction->card_type,
                'last_four' => $transaction->last_four,
            ]);

            return new PaymentRefund(
                success: true
            );
        } catch (HttpClientException $e) {
            return new PaymentRefund(
                success: false,
                message: $e->getMessage(),
            );
        }
    }
}
