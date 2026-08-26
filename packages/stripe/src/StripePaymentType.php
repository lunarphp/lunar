<?php

namespace Lunar\Stripe;

use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentCheck;
use Lunar\Base\DataTransferObjects\PaymentChecks;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Events\PaymentAttemptEvent;
use Lunar\Exceptions\Carts\CartException;
use Lunar\Exceptions\DisallowMultipleCartOrdersException;
use Lunar\Models\Contracts\Transaction as TransactionContract;
use Lunar\Models\Transaction;
use Lunar\PaymentTypes\AbstractPayment;
use Lunar\Stripe\Actions\UpdateOrderFromIntent;
use Lunar\Stripe\Events\OrphanedPaymentIntentDetected;
use Lunar\Stripe\Facades\Stripe;
use Lunar\Stripe\Managers\StripeManager;
use Lunar\Stripe\Models\StripePaymentIntent;
use Stripe\Exception\InvalidRequestException;
use Stripe\PaymentIntent;
use Stripe\StripeClient;

class StripePaymentType extends AbstractPayment
{
    /**
     * The Stripe instance.
     *
     * @var StripeClient
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
        $this->allowPartialPayment = config('lunar.stripe.allow_partial_payment', false);
    }

    /**
     * Authorize the payment for processing.
     */
    final public function authorize(): ?PaymentAuthorize
    {
        $paymentIntentId = $this->data['payment_intent'];

        $paymentIntentModel = StripePaymentIntent::where('intent_id', $paymentIntentId)->first();

        if ($paymentIntentModel && ! $paymentIntentModel->isActive()) {
            $failure = new PaymentAuthorize(
                success: false,
                message: 'Payment intent already processed',
                paymentType: 'stripe'
            );
            PaymentAttemptEvent::dispatch($failure);

            return $failure;
        }

        $this->order = $this->order ?: ($this->cart->draftOrder ?: $this->cart->completedOrder);

        if (($this->order && $this->order->isPlaced())) {
            $failure = new PaymentAuthorize(
                success: false,
                message: 'Order already placed',
                orderId: $this->order->id,
                paymentType: 'stripe'
            );
            PaymentAttemptEvent::dispatch($failure);

            return $failure;
        }

        $this->paymentIntent = $this->stripe->paymentIntents->retrieve(
            $paymentIntentId
        );

        if (! $this->paymentIntent) {
            $failure = new PaymentAuthorize(
                success: false,
                message: 'Unable to locate payment intent',
                orderId: $this->order?->id,
                paymentType: 'stripe',
            );

            PaymentAttemptEvent::dispatch($failure);

            return $failure;
        }

        if ($failure = $this->assertIntentMatchesTotal()) {
            return $failure;
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

        if ($this->paymentIntent->status == PaymentIntent::STATUS_REQUIRES_CAPTURE && $this->policy == 'automatic') {
            $this->paymentIntent = $this->stripe->paymentIntents->capture(
                $this->data['payment_intent']
            );
        }

        // Sync the Stripe-side status before any local order work so the row
        // never disagrees with reality even when downstream steps throw.
        $paymentIntentModel->status = $this->paymentIntent->status;
        $paymentIntentModel->save();

        if (! $this->order) {
            try {
                $this->order = $this->cart->createOrder();
                $paymentIntentModel->order_id = $this->order->id;
                $paymentIntentModel->save();
            } catch (DisallowMultipleCartOrdersException|CartException $e) {
                if ($this->paymentIntent->status === PaymentIntent::STATUS_SUCCEEDED) {
                    $paymentIntentModel->processed_at = now();
                    $paymentIntentModel->save();

                    OrphanedPaymentIntentDetected::dispatch(
                        $paymentIntentId,
                        $this->cart?->id,
                        $e->getMessage(),
                    );
                }

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
     * Verify the retrieved payment intent matches the expected order/cart total
     * and currency. Returns a failure DTO when the check fails, or null on pass.
     *
     * Subclasses may override to relax or extend the policy (e.g. per-order
     * deposit rules).
     */
    protected function assertIntentMatchesTotal(): ?PaymentAuthorize
    {
        if ($this->allowPartialPayment) {
            return null;
        }

        if ($this->order) {
            $expectedAmount = $this->order->total->value;
            $expectedCurrency = $this->order->currency_code;
            $currency = $this->order->currency;
        } else {
            $calculated = $this->cart->calculate();
            $expectedAmount = $calculated->total->value;
            $expectedCurrency = $calculated->currency->code;
            $currency = $calculated->currency;
        }

        // The intent amount is in Stripe's sub-unit scale, not Lunar's.
        $amountMatches = StripeManager::toStripeAmount($expectedAmount, $currency) === (int) $this->paymentIntent->amount;
        $currencyMatches = strtolower((string) $expectedCurrency) === strtolower((string) $this->paymentIntent->currency);

        if ($amountMatches && $currencyMatches) {
            return null;
        }

        $failure = new PaymentAuthorize(
            success: false,
            message: 'Payment intent amount does not match order total',
            orderId: $this->order?->id,
            paymentType: 'stripe',
        );

        PaymentAttemptEvent::dispatch($failure);

        return $failure;
    }

    /**
     * Capture a payment for a transaction.
     *
     * @param  int  $amount
     */
    public function capture(TransactionContract $transaction, $amount = 0): PaymentCapture
    {
        /** @var Transaction $transaction */
        $payload = [];

        if ($amount > 0) {
            $payload['amount_to_capture'] = StripeManager::toStripeAmount($amount, $transaction->order->currency);
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
    public function refund(TransactionContract $transaction, int $amount = 0, $notes = null): PaymentRefund
    {
        /** @var Transaction $transaction */
        $charge = Stripe::getCharge($transaction->reference);

        try {
            $refund = $this->stripe->refunds->create(
                ['payment_intent' => $charge->payment_intent, 'amount' => StripeManager::toStripeAmount($amount, $transaction->order->currency)]
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
            'amount' => StripeManager::fromStripeAmount($refund->amount, $transaction->order->currency),
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

    public function getPaymentChecks(TransactionContract $transaction): PaymentChecks
    {
        /** @var Transaction $transaction */
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
