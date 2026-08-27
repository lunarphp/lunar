<?php

namespace Lunar\Paypal;

use Illuminate\Http\Client\HttpClientException;
use Illuminate\Support\Collection;
use Lunar\Core\DataObjects\PaymentAuthorize;
use Lunar\Core\DataObjects\PaymentCapture;
use Lunar\Core\DataObjects\PaymentRefund;
use Lunar\Core\Events\PaymentAttemptEvent;
use Lunar\Core\Exceptions\Carts\CartException;
use Lunar\Core\Exceptions\DisallowMultipleCartOrdersException;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Transaction;
use Lunar\Core\PaymentTypes\AbstractPayment;
use Lunar\Paypal\Facades\Paypal;
use Lunar\Paypal\Managers\PaypalManager;

class PaypalPaymentType extends AbstractPayment
{
    /**
     * The policy when capturing payments.
     */
    protected string $policy;

    public function __construct()
    {
        $this->policy = config('lunar.paypal.policy', 'automatic');
        $this->allowPartialPayment = config('lunar.paypal.allow_partial_payment', false);
    }

    /**
     * Authorize the payment for processing.
     */
    public function authorize(): PaymentAuthorize
    {
        $this->order = $this->order ?: ($this->cart?->draftOrder ?: $this->cart?->completedOrder);

        if ($this->order?->placed_at) {
            return $this->fail('This order has already been placed');
        }

        $paypalOrderId = $this->data['paypal_order_id'] ?? null;

        if (! $paypalOrderId) {
            return $this->fail('No PayPal order reference was supplied');
        }

        $paypalOrder = Paypal::getOrder($paypalOrderId);

        if (($paypalOrder['name'] ?? null) === 'RESOURCE_NOT_FOUND') {
            return $this->fail('Unable to locate the PayPal order');
        }

        // Checked before capturing — a mismatch must not take the customer's money.
        if ($failure = $this->assertOrderMatchesTotal($paypalOrder)) {
            return $failure;
        }

        if (($paypalOrder['status'] ?? null) === 'APPROVED') {
            $paypalOrder = Paypal::capture($paypalOrderId);
        }

        if (($paypalOrder['status'] ?? null) !== 'COMPLETED') {
            return $this->fail('PayPal did not complete the payment');
        }

        if (! $this->order) {
            try {
                $this->order = $this->cart->createOrder();
            } catch (DisallowMultipleCartOrdersException|CartException $e) {
                return $this->fail($e->getMessage());
            }
        }

        $this->order->transactions()->createMany(
            $this->buildTransactions($paypalOrder, $this->order->currency)->all()
        );

        // payment_status and fulfilment_status are derived from the transaction
        // ledger by TransactionObserver, so placing the order is all that is left.
        $this->order->update([
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

    /**
     * Verify the PayPal order covers the expected total in the expected currency.
     *
     * An under-payment fails: the capture has not happened yet, so refusing here
     * costs nothing. An over-payment is allowed through — the money is the
     * customer's either way and the order's settlement state surfaces the excess,
     * which beats leaving a captured payment with no order attached.
     *
     * Subclasses may override to relax or extend the policy.
     *
     * @param  array<string, mixed>  $paypalOrder
     */
    protected function assertOrderMatchesTotal(array $paypalOrder): ?PaymentAuthorize
    {
        if ($this->allowPartialPayment) {
            return null;
        }

        if ($this->order) {
            $expectedAmount = $this->order->total;
            $currency = $this->order->currency;
        } else {
            $calculated = $this->cart->calculate();
            $expectedAmount = $calculated->total->value;
            $currency = $calculated->currency;
        }

        $units = collect($paypalOrder['purchase_units'] ?? []);

        $paypalCurrency = $units->first()['amount']['currency_code'] ?? null;

        if (strtolower((string) $paypalCurrency) !== strtolower((string) $currency->code)) {
            return $this->fail('PayPal order currency does not match the order currency');
        }

        $paypalAmount = $units->sum(
            fn (array $unit): int => PaypalManager::fromPaypalAmount(
                (string) ($unit['amount']['value'] ?? '0'),
                $currency
            )
        );

        if ($paypalAmount < $expectedAmount) {
            return $this->fail('PayPal order amount does not cover the order total');
        }

        return null;
    }

    /**
     * Build the transaction rows for every capture on the PayPal order.
     *
     * @param  array<string, mixed>  $paypalOrder
     * @return Collection<int, array<string, mixed>>
     */
    protected function buildTransactions(array $paypalOrder, Currency $currency): Collection
    {
        return collect($paypalOrder['purchase_units'] ?? [])
            ->flatMap(fn (array $unit): array => $unit['payments']['captures'] ?? [])
            ->map(fn (array $capture): array => [
                'success' => $capture['status'] === 'COMPLETED',
                'type' => 'capture',
                'driver' => 'paypal',
                'amount' => PaypalManager::fromPaypalAmount(
                    (string) ($capture['amount']['value'] ?? '0'),
                    $currency
                ),
                'reference' => $capture['id'],
                'status' => $capture['status'],
                'card_type' => 'paypal',
                'captured_at' => now()->parse($capture['create_time']),
                'meta' => $capture['processor_response'] ?? null,
            ]);
    }

    /**
     * Build, dispatch and return a failed authorization.
     */
    protected function fail(?string $message = null): PaymentAuthorize
    {
        $response = new PaymentAuthorize(
            success: false,
            message: $message,
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
    public function capture(Transaction $transaction, $amount = 0): PaymentCapture
    {
        return new PaymentCapture(success: true);
    }

    /**
     * Refund a captured transaction
     *
     * @param  string|null  $notes
     */
    public function refund(Transaction $transaction, int $amount = 0, $notes = null): PaymentRefund
    {
        $currency = $transaction->order->currency;

        try {
            $response = Paypal::refund(
                $transaction->reference,
                PaypalManager::toPaypalAmount($amount, $currency),
                $currency->code
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
