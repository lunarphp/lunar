<?php

namespace Lunar\Paypal;

use Illuminate\Support\Str;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Models\Transaction;
use Lunar\PaymentTypes\AbstractPayment;
use Lunar\Paypal\Facades\Paypal;
use Lunar\Models\Currency;

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
     *
     * @return \Lunar\Base\DataTransferObjects\PaymentAuthorize
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
            return new PaymentAuthorize(
                success: false,
                message: 'This order has already been placed',
            );
        }

        $paypalOrder = Paypal::getOrder(
            $paypalOrderId = $this->data['paypal_order_id']
        );

        if (isset($paypalOrder['name']) && $paypalOrder['name'] == 'RESOURCE_NOT_FOUND') {
            return new PaymentAuthorize(
                success: false,
            );
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

        return new PaymentAuthorize(
            success: true,
        );
    }

    private function failAuthorize()
    {
        return new PaymentAuthorize(
            success: false,
        );
    }

    /**
     * Capture a payment for a transaction.
     *
     * @param  \Lunar\Models\Transaction  $transaction
     * @param  int  $amount
     * @return \Lunar\Base\DataTransferObjects\PaymentCapture
     */
    public function capture(Transaction $transaction, $amount = 0): PaymentCapture
    {
        return new PaymentCapture(success: true);
    }

    /**
     * Refund a captured transaction
     *
     * @param  \Lunar\Models\Transaction  $transaction
     * @param  int  $amount
     * @param  string|null  $notes
     * @return \Lunar\Base\DataTransferObjects\PaymentRefund
     */
    public function refund(Transaction $transaction, int $amount = 0, $notes = null): PaymentRefund
    {

        return new PaymentRefund(
            success: true
        );
    }
}
