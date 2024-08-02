<?php

namespace Lunar\Stripe;

use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentCheck;
use Lunar\Base\DataTransferObjects\PaymentChecks;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Events\PaymentAttemptEvent;
use Lunar\Exceptions\DisallowMultipleCartOrdersException;
use Lunar\Models\Transaction;
use Lunar\PaymentTypes\AbstractPayment;
use Lunar\Stripe\Actions\UpdateOrderFromIntent;
use Lunar\Stripe\Facades\Stripe;
use Lunar\Stripe\Models\StripePaymentIntent;
use Stripe\Exception\InvalidRequestException;
use Stripe\PaymentIntent;

class StripePaymentType extends AbstractPayment
{
    /**
     * The Stripe instance.
     *
     * @var \Stripe\StripeClient
     */
    protected $stripe;

    /**
     * The Payment intent.
     */
    protected PaymentIntent $paymentIntent;

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
        $this->stripe = Stripe::getClient();

        $this->policy = config('lunar.stripe.policy', 'automatic');
    }

    /**
     * Authorize the payment for processing.
     */
    final public function authorize(): ?PaymentAuthorize
    {
        $paymentIntentId = $this->data['payment_intent'];

        $paymentIntentModel = StripePaymentIntent::where('intent_id', $paymentIntentId)->first();

        $this->order = $this->order ?: ($this->cart->draftOrder ?: $this->cart->completedOrder);

        if (($this->order && $this->order->placed_at) || $paymentIntentModel?->processing_at) {
            return null;
        }

        if (! $paymentIntentModel) {
            $paymentIntentModel = StripePaymentIntent::create([
                'intent_id' => $paymentIntentId,
                'cart_id' => $this->cart?->id ?: $this->order->cart_id,
                'order_id' => $this->order?->id,
            ]);
        }

        $paymentIntentModel->update([
            'processing_at' => now(),
        ]);

        if (! $this->order) {
            try {
                $this->order = $this->cart->createOrder();
                $paymentIntentModel->order_id = $this->order->id;
            } catch (DisallowMultipleCartOrdersException $e) {
                $failure = new PaymentAuthorize(
                    success: false,
                    message: $e->getMessage(),
                    orderId: $this->order?->id,
                    paymentType: 'stripe'
                );
                PaymentAttemptEvent::dispatch($failure);

                return $failure;
            }
        }

        $this->paymentIntent = $this->stripe->paymentIntents->retrieve(
            $paymentIntentId
        );

        if (! $this->paymentIntent) {
            $failure = new PaymentAuthorize(
                success: false,
                message: 'Unable to locate payment intent',
                orderId: $this->order->id,
                paymentType: 'stripe',
            );

            PaymentAttemptEvent::dispatch($failure);

            return $failure;
        }

        if ($this->paymentIntent->status == PaymentIntent::STATUS_REQUIRES_CAPTURE && $this->policy == 'automatic') {
            $this->paymentIntent = $this->stripe->paymentIntents->capture(
                $this->data['payment_intent']
            );
        }

        $paymentIntentModel->status = $this->paymentIntent->status;

        $order = (new UpdateOrderFromIntent)->execute(
            $this->order,
            $this->paymentIntent
        );

        $response = new PaymentAuthorize(
            success: (bool) $order->placed_at,
            message: $this->paymentIntent->last_payment_error,
            orderId: $order->id,
            paymentType: 'stripe',
        );

        PaymentAttemptEvent::dispatch($response);

        $paymentIntentModel->processed_at = now();

        $paymentIntentModel->save();

        return $response;
    }

    /**
     * Capture a payment for a transaction.
     *
     * @param  int  $amount
     */
    public function capture(Transaction $transaction, $amount = 0): PaymentCapture
    {
        $payload = [];

        if ($amount > 0) {
            $payload['amount_to_capture'] = $amount;
        }

        $charge = Stripe::getCharge($transaction->reference);

        $paymentIntent = Stripe::fetchIntent($charge->payment_intent);

        try {
            $response = $this->stripe->paymentIntents->capture(
                $paymentIntent->id,
                $payload
            );
        } catch (InvalidRequestException $e) {
            return new PaymentCapture(
                success: false,
                message: $e->getMessage()
            );
        }

        UpdateOrderFromIntent::execute($transaction->order, $paymentIntent);

        return new PaymentCapture(success: true);
    }

    /**
     * Refund a captured transaction
     *
     * @param  string|null  $notes
     */
    public function refund(Transaction $transaction, int $amount = 0, $notes = null): PaymentRefund
    {
        $charge = Stripe::getCharge($transaction->reference);

        try {
            $refund = $this->stripe->refunds->create(
                ['payment_intent' => $charge->payment_intent, 'amount' => $amount]
            );
        } catch (InvalidRequestException $e) {
            return new PaymentRefund(
                success: false,
                message: $e->getMessage()
            );
        }

        $transaction->order->transactions()->create([
            'success' => $refund->status != 'failed',
            'type' => 'refund',
            'driver' => 'stripe',
            'amount' => $refund->amount,
            'reference' => $refund->payment_intent,
            'status' => $refund->status,
            'notes' => $notes,
            'card_type' => $transaction->card_type,
            'last_four' => $transaction->last_four,
        ]);

        return new PaymentRefund(
            success: true
        );
    }

    public function getPaymentChecks(Transaction $transaction): PaymentChecks
    {
        $meta = $transaction->meta;

        $checks = new PaymentChecks;

        if (isset($meta['address_line1_check'])) {
            $checks->addCheck(
                new PaymentCheck(
                    successful: $meta['address_line1_check'] == 'pass',
                    label: 'Address Line 1',
                    message: $meta['address_line1_check'],
                )
            );
        }

        if (isset($meta['address_postal_code_check'])) {
            $checks->addCheck(
                new PaymentCheck(
                    successful: $meta['address_postal_code_check'] == 'pass',
                    label: 'Postal Code',
                    message: $meta['address_postal_code_check'],
                )
            );
        }

        if (isset($meta['cvc_check'])) {
            $checks->addCheck(
                new PaymentCheck(
                    successful: $meta['cvc_check'] == 'pass',
                    label: 'CVC Check',
                    message: $meta['cvc_check'],
                )
            );
        }

        return $checks;
    }
}
