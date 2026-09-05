<?php

namespace Lunar\Paypal;

use Illuminate\Http\Client\HttpClientException;
use Illuminate\Support\Collection;
use Lunar\Core\DataObjects\PaymentAuthorize;
use Lunar\Core\DataObjects\PaymentCapture;
use Lunar\Core\DataObjects\PaymentCheck;
use Lunar\Core\DataObjects\PaymentChecks;
use Lunar\Core\DataObjects\PaymentRefund;
use Lunar\Core\Events\PaymentAttemptEvent;
use Lunar\Core\Exceptions\Carts\CartException;
use Lunar\Core\Exceptions\DisallowMultipleCartOrdersException;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Transaction;
use Lunar\Core\PaymentTypes\AbstractPayment;
use Lunar\Paypal\Facades\Paypal;
use Lunar\Paypal\Managers\PaypalManager;
use Lunar\Paypal\Models\PaypalOrder;

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

        $paypalOrderModel = PaypalOrder::firstOrCreate(
            ['paypal_order_id' => $paypalOrderId],
            ['cart_id' => $this->cart?->id ?: $this->order?->cart_id, 'order_id' => $this->order?->id],
        );

        if ($paypalOrderModel->isProcessed()) {
            return $this->fail('This PayPal order has already been processed');
        }

        $paypalOrderModel->update(['processing_at' => now()]);

        // Checked before capturing — a mismatch must not take the customer's money.
        if ($failure = $this->assertOrderMatchesTotal($paypalOrder)) {
            return $failure;
        }

        $manual = $this->policy === 'manual';

        if (($paypalOrder['status'] ?? null) === 'APPROVED') {
            $paypalOrder = $manual
                ? Paypal::authorizeOrder($paypalOrderId, $paypalOrderId)
                : Paypal::capture($paypalOrderId, $paypalOrderId);
        }

        // Sync the PayPal-side status before any local work, so the row cannot
        // disagree with reality if a later step throws.
        $paypalOrderModel->update(['status' => $paypalOrder['status'] ?? null]);

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

        $transactions = $this->buildTransactions($paypalOrder, $this->order->currency);

        if ($transactions->isEmpty()) {
            return $this->fail('PayPal returned no captures or authorizations');
        }

        $this->order->transactions()->createMany($transactions->all());

        // payment_status and fulfilment_status are derived from the transaction
        // ledger by TransactionObserver, so placing the order is all that is left.
        $this->order->update([
            'placed_at' => now(),
        ]);

        $paypalOrderModel->update([
            'order_id' => $this->order->id,
            'processed_at' => now(),
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

        // Compare at PayPal's precision for the currency — for one PayPal treats
        // as zero-decimal, a subunit total can never be matched exactly, so
        // holding PayPal to the raw minor-unit total would refuse every total
        // that rounds down.
        $expectedAmount = PaypalManager::fromPaypalAmount(
            PaypalManager::toPaypalAmount($expectedAmount, $currency),
            $currency,
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
        $units = collect($paypalOrder['purchase_units'] ?? []);

        // Under the manual policy PayPal returns authorizations rather than
        // captures. They become `intent` transactions, which the ledger reads as
        // authorized-but-not-paid until capture() converts them.
        return $units
            ->flatMap(fn (array $unit): array => $unit['payments']['captures'] ?? [])
            ->map(fn (array $capture): array => $this->transactionRow($capture, $currency, 'capture'))
            ->merge(
                $units
                    ->flatMap(fn (array $unit): array => $unit['payments']['authorizations'] ?? [])
                    ->map(fn (array $auth): array => $this->transactionRow($auth, $currency, 'intent'))
            )
            ->values();
    }

    /**
     * Map a PayPal capture or authorization onto a transaction row.
     *
     * @param  array<string, mixed>  $payment
     * @return array<string, mixed>
     */
    protected function transactionRow(array $payment, Currency $currency, string $type): array
    {
        $succeeded = in_array($payment['status'] ?? null, ['COMPLETED', 'CREATED'], true);

        return [
            'success' => $succeeded,
            'type' => $type,
            'driver' => 'paypal',
            'amount' => PaypalManager::fromPaypalAmount(
                (string) ($payment['amount']['value'] ?? '0'),
                $currency
            ),
            'reference' => $payment['id'],
            'status' => $payment['status'] ?? null,
            'card_type' => 'paypal',
            'captured_at' => $type === 'capture' && $succeeded
                ? now()->parse($payment['create_time'])
                : null,
            'meta' => $payment['processor_response'] ?? null,
        ];
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
        /** @var Transaction $transaction */
        $currency = $transaction->order->currency;

        // Zero means "capture the whole authorization".
        $amount = $amount > 0 ? $amount : $transaction->amount;

        try {
            $response = Paypal::captureAuthorization(
                $transaction->reference,
                PaypalManager::toPaypalAmount($amount, $currency),
                $currency->code,
                $transaction->reference.':'.$amount,
            );
        } catch (HttpClientException $e) {
            return new PaymentCapture(
                success: false,
                message: $e->getMessage(),
            );
        }

        if (($response['status'] ?? null) !== 'COMPLETED') {
            return new PaymentCapture(
                success: false,
                message: 'PayPal did not complete the capture',
            );
        }

        $transaction->order->transactions()->create([
            'success' => true,
            'type' => 'capture',
            'driver' => 'paypal',
            'amount' => PaypalManager::fromPaypalAmount(
                (string) ($response['amount']['value'] ?? '0'),
                $currency
            ),
            'reference' => $response['id'],
            'status' => $response['status'],
            'card_type' => $transaction->card_type,
            'last_four' => $transaction->last_four,
            'captured_at' => now(),
        ]);

        return new PaymentCapture(success: true);
    }

    /**
     * Surface the AVS and CVV results PayPal returns on a capture.
     *
     * Codes vary by card network; the pass sets below are the ones PayPal
     * documents as a match for every network.
     *
     * @see https://developer.paypal.com/docs/api/payments/v2/#definition-processor_response
     */
    public function getPaymentChecks(Transaction $transaction): PaymentChecks
    {
        /** @var Transaction $transaction */
        $meta = $transaction->meta;

        $checks = new PaymentChecks;

        if ($avs = ($meta['avs_code'] ?? null)) {
            $checks->addCheck(new PaymentCheck(
                // Y and X are full matches; A and B match the street only.
                successful: in_array($avs, ['A', 'B', 'D', 'F', 'M', 'X', 'Y'], true),
                label: __('lunar-paypal::checks.avs'),
                message: $avs,
            ));
        }

        if ($cvv = ($meta['cvv_code'] ?? null)) {
            $checks->addCheck(new PaymentCheck(
                successful: in_array($cvv, ['M', 'Y'], true),
                label: __('lunar-paypal::checks.cvv'),
                message: $cvv,
            ));
        }

        return $checks;
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
                $currency->code,
                $transaction->reference.':refund:'.$amount,
            );

            $refundTransaction = $transaction->order->transactions()->create([
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
                success: true,
                transaction: $refundTransaction,
            );
        } catch (HttpClientException $e) {
            return new PaymentRefund(
                success: false,
                message: $e->getMessage(),
            );
        }
    }
}
