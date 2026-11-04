<?php

namespace Lunar\Stripe;

use Lunar\Core\Contracts\SupportsPaymentHolds;
use Lunar\Core\Contracts\SyncsPaymentIntents;
use Lunar\Core\DataObjects\PaymentAuthorize;
use Lunar\Core\DataObjects\PaymentCapture;
use Lunar\Core\DataObjects\PaymentCheck;
use Lunar\Core\DataObjects\PaymentChecks;
use Lunar\Core\DataObjects\PaymentIntentDescriptor;
use Lunar\Core\DataObjects\PaymentIntentSnapshot;
use Lunar\Core\DataObjects\PaymentRefund;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Enums\HoldAdjustment;
use Lunar\Core\Enums\PaymentIntentStatus;
use Lunar\Core\Events\PaymentAttemptEvent;
use Lunar\Core\Exceptions\Carts\CartException;
use Lunar\Core\Exceptions\DisallowMultipleCartOrdersException;
use Lunar\Core\Exceptions\PaymentIntentException;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Transaction;
use Lunar\Core\PaymentTypes\AbstractPayment;
use Lunar\Stripe\Actions\UpdateOrderFromIntent;
use Lunar\Stripe\Events\OrphanedPaymentIntentDetected;
use Lunar\Stripe\Facades\Stripe;
use Lunar\Stripe\Managers\StripeManager;
use Lunar\Stripe\Models\StripePaymentIntent;
use Stripe\Charge;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\InvalidRequestException;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\StripeClient;

class StripePaymentType extends AbstractPayment implements SupportsPaymentHolds, SyncsPaymentIntents
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
     * {@inheritDoc}
     *
     * Delegates to the manager, which reuses the cart's live standard intent
     * and sends an idempotency key, so a retry after a lost response recovers
     * the intent rather than minting a second one.
     */
    public function createIntent(Cart $cart): PaymentIntentDescriptor
    {
        try {
            return $this->describeCreated(Stripe::createIntent($cart));
        } catch (ApiErrorException $e) {
            throw new PaymentIntentException(
                "Could not create a Stripe payment intent for cart [{$cart->id}].", previous: $e,
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function syncIntent(Cart $cart): void
    {
        try {
            Stripe::syncIntent($cart);
        } catch (ApiErrorException $e) {
            throw new PaymentIntentException(
                "Could not sync the Stripe payment intent for cart [{$cart->id}].", previous: $e,
            );
        }
    }

    /**
     * {@inheritDoc}
     *
     * Delegates to the manager, which mints a manual-capture intent with
     * incremental authorization requested where the rail offers it.
     */
    public function createHold(Cart $cart): PaymentIntentDescriptor
    {
        try {
            return $this->describeCreated(Stripe::createHold($cart));
        } catch (ApiErrorException $e) {
            throw new PaymentIntentException(
                "Could not create a Stripe hold for cart [{$cart->id}].", previous: $e,
            );
        }
    }

    private function describeCreated(PaymentIntent $intent): PaymentIntentDescriptor
    {
        return new PaymentIntentDescriptor(
            reference: $intent->id,
            clientPayload: ['client_secret' => $intent->client_secret],
        );
    }

    /**
     * {@inheritDoc}
     *
     * Refunded comes from the latest charge's `amount_refunded`, which Stripe
     * raises when a refund is created, pending included.
     */
    public function fetchIntent(Cart $cart, string $reference): ?PaymentIntentSnapshot
    {
        if (! $this->ownsIntent($cart, $reference)) {
            return null;
        }

        $intent = $this->retrieveIntent($reference, ['latest_charge', 'payment_method']);
        $charge = $this->latestCharge($intent);
        $currency = $this->intentCurrency($cart, $intent);
        $price = fn (int $stripeAmount) => new PriceValue(StripeManager::fromStripeAmount($stripeAmount, $currency), $currency);

        return new PaymentIntentSnapshot(
            status: $this->intentStatus($intent),
            amount: $price((int) $intent->amount),
            captured: $price((int) $intent->amount_received),
            refunded: $price($charge ? (int) $charge->amount_refunded : 0),
            walletLabel: $this->walletLabel($intent),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function voidIntent(Cart $cart, string $reference): void
    {
        $this->assertOwnsIntent($cart, $reference);

        $intent = $this->retrieveIntent($reference);

        match ($this->intentStatus($intent)) {
            PaymentIntentStatus::Voided,
            PaymentIntentStatus::Failed => null,
            PaymentIntentStatus::Processing => throw new PaymentIntentException(
                "Stripe payment intent [{$reference}] is processing; void it once it settles.",
            ),
            PaymentIntentStatus::Captured => throw new PaymentIntentException(
                "Stripe payment intent [{$reference}] is captured; refund it instead.",
            ),
            PaymentIntentStatus::RequiresAction,
            PaymentIntentStatus::RequiresCapture => $this->gateway(
                fn () => $this->stripe->paymentIntents->cancel($reference),
                "Could not void Stripe payment intent [{$reference}].",
            ),
        };

        $this->recordStatus($cart, $reference, PaymentIntent::STATUS_CANCELED);
    }

    /**
     * {@inheritDoc}
     */
    public function refundIntent(Cart $cart, string $reference, PriceValue $amount, string $idempotencyKey): PaymentRefund
    {
        $this->assertOwnsIntent($cart, $reference);

        $intent = $this->retrieveIntent($reference, ['latest_charge']);

        if ($intent->status !== PaymentIntent::STATUS_SUCCEEDED) {
            throw new PaymentIntentException(
                "Stripe payment intent [{$reference}] is not captured, so there is nothing to refund.",
            );
        }

        $charge = $this->latestCharge($intent);
        $remaining = (int) $intent->amount_received - ($charge ? (int) $charge->amount_refunded : 0);
        $stripeAmount = $this->toStripeAmount($intent, $amount);

        if ($stripeAmount > $remaining) {
            throw new PaymentIntentException(
                "Cannot refund {$stripeAmount} on Stripe payment intent [{$reference}], which has {$remaining} left to refund.",
            );
        }

        $refund = $this->gateway(
            fn () => $this->stripe->refunds->create(
                ['payment_intent' => $reference, 'amount' => $stripeAmount],
                ['idempotency_key' => $idempotencyKey],
            ),
            "Could not refund Stripe payment intent [{$reference}].",
        );

        if (in_array($refund->status, ['failed', 'canceled'], true)) {
            throw new PaymentIntentException(
                "Stripe refused the refund of payment intent [{$reference}] with status [{$refund->status}].",
            );
        }

        return new PaymentRefund(
            success: true,
            reference: $refund->id,
            pending: $refund->status !== 'succeeded',
        );
    }

    /**
     * {@inheritDoc}
     */
    public function adjustHold(Cart $cart, string $reference, PriceValue $amount): HoldAdjustment
    {
        $this->assertOwnsIntent($cart, $reference);

        $intent = $this->retrieveIntent($reference, ['latest_charge']);
        $this->assertAwaitingCapture($intent);

        $stripeAmount = $this->toStripeAmount($intent, $amount);

        if ($stripeAmount <= (int) $intent->amount) {
            return HoldAdjustment::Ok;
        }

        if (! $this->supportsIncrementalAuthorization($intent)) {
            return HoldAdjustment::NeedsReauthorization;
        }

        try {
            $this->stripe->paymentIntents->incrementAuthorization($reference, ['amount' => $stripeAmount]);
        } catch (InvalidRequestException) {
            // The rail advertised support but refused this increment (over
            // the issuer's limit, authorisation aged out).
            return HoldAdjustment::NeedsReauthorization;
        } catch (ApiErrorException $e) {
            throw new PaymentIntentException(
                "Could not confirm the outcome of adjusting Stripe hold [{$reference}].", previous: $e,
            );
        }

        return HoldAdjustment::Ok;
    }

    /**
     * {@inheritDoc}
     */
    public function captureHold(Cart $cart, string $reference, PriceValue $amount): void
    {
        $this->assertOwnsIntent($cart, $reference);

        $intent = $this->retrieveIntent($reference);
        $stripeAmount = $this->toStripeAmount($intent, $amount);

        if ($intent->status === PaymentIntent::STATUS_SUCCEEDED) {
            if ((int) $intent->amount_received !== $stripeAmount) {
                throw new PaymentIntentException(
                    "Stripe hold [{$reference}] was already captured for {$intent->amount_received}, not {$stripeAmount}.",
                );
            }

            return;
        }

        $this->assertAwaitingCapture($intent);

        if ($stripeAmount > (int) $intent->amount_capturable) {
            throw new PaymentIntentException(
                "Cannot capture {$stripeAmount} on Stripe hold [{$reference}], which authorised {$intent->amount_capturable}.",
            );
        }

        $this->gateway(
            fn () => $this->stripe->paymentIntents->capture($reference, ['amount_to_capture' => $stripeAmount]),
            "Could not capture Stripe hold [{$reference}].",
        );

        $this->recordStatus($cart, $reference, PaymentIntent::STATUS_SUCCEEDED);
    }

    /**
     * Whether the authorised charge can be incremented. Stripe reports this
     * on the charge, not the intent: online cards carry
     * `payment_method_details.card.incremental_authorization.status`
     * ("available" / "unavailable"), Terminal carries
     * `payment_method_details.card_present.incremental_authorization_supported`.
     * Requires `latest_charge` expanded on the intent.
     */
    protected function supportsIncrementalAuthorization(PaymentIntent $intent): bool
    {
        $charge = $intent->latest_charge;

        if (! $charge instanceof Charge) {
            return false;
        }

        $details = $charge->payment_method_details;

        if (($details?->card?->incremental_authorization?->status ?? null) === 'available') {
            return true;
        }

        return (bool) ($details?->card_present?->incremental_authorization_supported ?? false);
    }

    /**
     * @throws PaymentIntentException for a status this driver does not know,
     *                                rather than guess one
     */
    private function intentStatus(PaymentIntent $intent): PaymentIntentStatus
    {
        return match ($intent->status) {
            // A declined confirmation drops back to requires_payment_method
            // with the error recorded. The intent is still live and the
            // customer can retry it with another card, so it is not Failed.
            PaymentIntent::STATUS_REQUIRES_PAYMENT_METHOD,
            PaymentIntent::STATUS_REQUIRES_CONFIRMATION,
            PaymentIntent::STATUS_REQUIRES_ACTION => PaymentIntentStatus::RequiresAction,
            PaymentIntent::STATUS_PROCESSING => PaymentIntentStatus::Processing,
            PaymentIntent::STATUS_REQUIRES_CAPTURE => PaymentIntentStatus::RequiresCapture,
            PaymentIntent::STATUS_SUCCEEDED => PaymentIntentStatus::Captured,
            PaymentIntent::STATUS_CANCELED => PaymentIntentStatus::Voided,
            default => throw new PaymentIntentException(
                "Stripe payment intent [{$intent->id}] has an unrecognised status [{$intent->status}].",
            ),
        };
    }

    private function ownsIntent(Cart $cart, string $reference): bool
    {
        return $cart->paymentIntents()->where('intent_id', $reference)->exists();
    }

    private function assertOwnsIntent(Cart $cart, string $reference): void
    {
        if (! $this->ownsIntent($cart, $reference)) {
            throw new PaymentIntentException(
                "Stripe payment intent [{$reference}] was not created for cart [{$cart->id}].",
            );
        }
    }

    private function assertAwaitingCapture(PaymentIntent $intent): void
    {
        if ($intent->status !== PaymentIntent::STATUS_REQUIRES_CAPTURE) {
            throw new PaymentIntentException(
                "Stripe hold [{$intent->id}] is [{$intent->status}], not awaiting capture.",
            );
        }
    }

    private function recordStatus(Cart $cart, string $reference, string $status): void
    {
        $cart->paymentIntents()->where('intent_id', $reference)->update(['status' => $status]);
    }

    /**
     * @param  array<int, string>  $expand
     */
    private function retrieveIntent(string $reference, array $expand = []): PaymentIntent
    {
        return $this->gateway(
            fn () => $this->stripe->paymentIntents->retrieve($reference, $expand ? ['expand' => $expand] : []),
            "Could not read Stripe payment intent [{$reference}].",
        );
    }

    private function latestCharge(PaymentIntent $intent): ?Charge
    {
        if (is_string($intent->latest_charge)) {
            return $this->gateway(
                fn () => $this->stripe->charges->retrieve($intent->latest_charge),
                "Could not read the charge for Stripe payment intent [{$intent->id}].",
            );
        }

        return $intent->latest_charge instanceof Charge ? $intent->latest_charge : null;
    }

    /**
     * The currency to scale the intent's amounts by: the cart's, or the one
     * the intent is still in when the cart has since changed currency.
     */
    private function intentCurrency(Cart $cart, PaymentIntent $intent): Currency
    {
        if (strtolower((string) $intent->currency) === strtolower((string) $cart->currency->code)) {
            return $cart->currency;
        }

        return Currency::where('code', strtoupper((string) $intent->currency))->first()
            ?? throw new PaymentIntentException(
                "Stripe payment intent [{$intent->id}] is in [{$intent->currency}], which this store does not have.",
            );
    }

    /**
     * The amount in Stripe's sub-units, refusing one in another currency
     * from the intent rather than charging the wrong figure.
     */
    private function toStripeAmount(PaymentIntent $intent, PriceValue $amount): int
    {
        $currency = $amount->resolveCurrency();

        if (strtolower((string) $currency->code) !== strtolower((string) $intent->currency)) {
            throw new PaymentIntentException(
                "Stripe payment intent [{$intent->id}] is in [{$intent->currency}], not [{$currency->code}].",
            );
        }

        return StripeManager::toStripeAmount($amount->value, $currency);
    }

    private function walletLabel(PaymentIntent $intent): ?string
    {
        $method = $intent->payment_method;

        if (! $method instanceof PaymentMethod) {
            return null;
        }

        return match ($method->card?->wallet?->type ?? null) {
            'apple_pay' => 'Apple Pay',
            'google_pay' => 'Google Pay',
            'link' => 'Link',
            default => null,
        };
    }

    /**
     * Run a Stripe call, reporting any API failure on the one channel the
     * capabilities have.
     *
     * @template T
     *
     * @param  callable(): T  $call
     * @return T
     */
    private function gateway(callable $call, string $message): mixed
    {
        try {
            return $call();
        } catch (ApiErrorException $e) {
            throw new PaymentIntentException($message, previous: $e);
        }
    }

    /**
     * Authorize the payment for processing.
     */
    public function authorize(): ?PaymentAuthorize
    {
        $paymentIntentId = $this->data['reference'] ?? $this->data['payment_intent'];

        $paymentIntentModel = StripePaymentIntent::where('intent_id', $paymentIntentId)->first();

        // Idempotent: the consumer and the webhook can both place the same
        // intent, and whichever arrives second gets the order already placed.
        // Only for the intent's own cart; any other cart falls through to the
        // "already processed" failure below.
        $cartId = $this->cart?->id ?: $this->order?->cart_id;

        if ($paymentIntentModel && $paymentIntentModel->cart_id === $cartId && $paymentIntentModel->order?->isPlaced()) {
            return new PaymentAuthorize(
                success: true,
                orderId: $paymentIntentModel->order_id,
                paymentType: 'stripe',
            );
        }

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
                /**
                 * This row is minted here rather than by
                 * StripeManager::createIntent()/createHold(), so there is no
                 * flavour argument to trust; infer it from the fetched
                 * intent instead of defaulting to standard, or a hold intent
                 * reaching this fallback would cross-contaminate flavour-keyed
                 * intent reuse.
                 */
                'flavour' => $this->paymentIntent->capture_method === 'manual' ? 'hold' : 'standard',
            ]);
        }

        $paymentIntentModel->update([
            'processing_at' => now(),
        ]);

        if ($this->paymentIntent->status == PaymentIntent::STATUS_REQUIRES_CAPTURE && $this->policy == 'automatic') {
            $this->paymentIntent = $this->stripe->paymentIntents->capture($paymentIntentId);
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
            $expectedAmount = $this->order->total;
            $expectedCurrency = $this->order->currency_code;
            $currency = $this->order->currency;
        } else {
            $calculated = $this->cart->calculate();
            $expectedAmount = $calculated->total->value;
            $expectedCurrency = $calculated->currency->code;
            $currency = $calculated->currency;
        }

        // The intent amount is in Stripe's sub-unit scale, not Lunar's. A
        // captured hold may have taken less than it authorised, so a captured
        // intent is compared by what was captured.
        $intentAmount = $this->paymentIntent->status === PaymentIntent::STATUS_SUCCEEDED
            ? (int) $this->paymentIntent->amount_received
            : (int) $this->paymentIntent->amount;

        $amountMatches = StripeManager::toStripeAmount($expectedAmount, $currency) === $intentAmount;
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
    public function capture(Transaction $transaction, $amount = 0): PaymentCapture
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
    public function refund(Transaction $transaction, int $amount = 0, $notes = null): PaymentRefund
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

        $refundTransaction = $transaction->order->transactions()->create([
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
            success: true,
            transaction: $refundTransaction,
        );
    }

    public function getPaymentChecks(Transaction $transaction): PaymentChecks
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
