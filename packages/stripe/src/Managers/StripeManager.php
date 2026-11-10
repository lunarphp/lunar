<?php

namespace Lunar\Stripe\Managers;

use Illuminate\Support\Collection;
use Lunar\Models\Cart;
use Lunar\Models\Contracts\Cart as CartContract;
use Lunar\Models\Contracts\Currency as CurrencyContract;
use Lunar\Stripe\Enums\CancellationReason;
use Stripe\Charge;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\InvalidRequestException;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Stripe;
use Stripe\StripeClient;

class StripeManager
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.key'));
    }

    /**
     * Return the Stripe client
     */
    public function getClient(): StripeClient
    {
        return new StripeClient([
            'api_key' => config('services.stripe.key'),
        ]);
    }

    public function getCartIntentId(CartContract $cart): ?string
    {
        return $cart->meta['payment_intent'] ?? $cart->paymentIntents()->active()->first()?->intent_id;
    }

    public function fetchOrCreateIntent(CartContract $cart, array $createOptions = []): PaymentIntent
    {
        /** @var Cart $cart */
        $existingIntentId = $this->getCartIntentId($cart);

        $intent = $existingIntentId ? $this->fetchIntent($existingIntentId) : $this->createIntent($cart, $createOptions);

        /**
         * If the payment intent is stored in the meta, we don't have a linked payment intent
         * then it's a "legacy" cart, we should make a new record.
         */
        if (! empty($cart->meta['payment_intent']) && ! $cart->paymentIntents->first()) {
            $cart->paymentIntents()->create([
                'intent_id' => $intent->id,
                'status' => $intent->status,
            ]);
        }

        return $intent;
    }

    public function getPaymentMethod(string $paymentMethodId): ?PaymentMethod
    {
        try {
            return PaymentMethod::retrieve($paymentMethodId);
        } catch (ApiErrorException $e) {
        }

        return null;
    }

    /**
     * Create a payment intent from a Cart
     */
    public function createIntent(CartContract $cart, array $opts = []): PaymentIntent
    {
        /** @var Cart $cart */
        $existingId = $this->getCartIntentId($cart);

        if (
            $existingId &&
            $intent = $this->fetchIntent(
                $existingId
            )
        ) {
            return $intent;
        }

        $paymentIntent = $this->buildIntent(
            static::toStripeAmount($cart->total->value, $cart->currency),
            $cart->currency->code,
            $opts
        );

        $cart->paymentIntents()->create([
            'intent_id' => $paymentIntent->id,
            'status' => $paymentIntent->status,
        ]);

        return $paymentIntent;
    }

    public function updateShippingAddress(CartContract $cart): void
    {
        /** @var Cart $cart */
        $address = $cart->shippingAddress;

        if ($address) {
            $this->updateIntent($cart, [
                'shipping' => [
                    'name' => "{$address->first_name} {$address->last_name}",
                    'phone' => $address->contact_phone,
                    'address' => [
                        'city' => $address->city,
                        'country' => $address->country->iso2,
                        'line1' => $address->line_one,
                        'line2' => $address->line_two,
                        'postal_code' => $address->postcode,
                        'state' => $address->state,
                    ],
                ],
            ]);
        }
    }

    public function updateIntent(CartContract $cart, array $values): void
    {
        /** @var Cart $cart */
        $intentId = $this->getCartIntentId($cart);

        if (! $intentId) {
            return;
        }

        $this->updateIntentById($intentId, $values);
    }

    public function updateIntentById(string $id, array $values): void
    {
        $this->getClient()->paymentIntents->update(
            $id,
            $values
        );
    }

    public function syncIntent(CartContract $cart): void
    {
        /** @var Cart $cart */
        $intentId = $this->getCartIntentId($cart);

        if (! $intentId) {
            return;
        }

        $cart = $cart->calculate();

        $this->getClient()->paymentIntents->update(
            $intentId,
            ['amount' => static::toStripeAmount($cart->total->value, $cart->currency)]
        );
    }

    public function cancelIntent(CartContract $cart, CancellationReason $reason): void
    {
        /** @var Cart $cart */
        $intentId = $this->getCartIntentId($cart);

        if (! $intentId) {
            return;
        }

        try {
            $this->getClient()->paymentIntents->cancel(
                $intentId,
                ['cancellation_reason' => $reason->value]
            );
            $cart->paymentIntents()->where('intent_id', $intentId)->update([
                'status' => PaymentIntent::STATUS_CANCELED,
                'processing_at' => now(),
                'processed_at' => now(),
            ]);
        } catch (\Exception $e) {

        }
    }

    /**
     * Fetch an intent from the Stripe API.
     */
    public function fetchIntent(string $intentId, $options = null): ?PaymentIntent
    {
        try {
            $intent = PaymentIntent::retrieve($intentId, $options);
        } catch (InvalidRequestException $e) {
            return null;
        }

        return $intent;
    }

    public function getCharges(string $paymentIntentId): Collection
    {
        try {
            return collect(
                $this->getClient()->charges->all([
                    'payment_intent' => $paymentIntentId,
                ])['data'] ?? null
            );
        } catch (\Exception $e) {
            //
        }

        return collect();
    }

    public function getCharge(string $chargeId): Charge
    {
        return $this->getClient()->charges->retrieve($chargeId);
    }

    /**
     * Zero-decimal currencies, per Stripe. The amount sent to Stripe is the
     * major unit amount as-is.
     *
     * @see https://docs.stripe.com/currencies#zero-decimal
     */
    protected const ZERO_DECIMAL_CURRENCIES = [
        'bif', 'clp', 'djf', 'gnf', 'jpy', 'kmf', 'krw', 'mga', 'pyg',
        'rwf', 'ugx', 'vnd', 'vuv', 'xaf', 'xof', 'xpf',
    ];

    /**
     * Three-decimal currencies, per Stripe. The amount sent to Stripe is the
     * major unit amount multiplied by 1000.
     *
     * @see https://docs.stripe.com/currencies#three-decimal
     */
    protected const THREE_DECIMAL_CURRENCIES = ['bhd', 'jod', 'kwd', 'omr', 'tnd'];

    /**
     * HUF, TWD and UGX are ISO zero-decimal currencies, but Stripe still
     * requires amounts to be sent as if they had two decimal places.
     *
     * @see https://docs.stripe.com/currencies#special-cases
     */
    protected const SPECIAL_ZERO_DECIMAL_CURRENCIES = ['huf', 'twd', 'ugx'];

    /**
     * Convert a Lunar price value to the amount expected by Stripe.
     *
     * Lunar stores prices as integers scaled by `Currency::decimal_places`,
     * which merchants can set independently of what Stripe expects for a
     * given currency. This converts back to the major unit amount first,
     * then re-scales it to whatever sub-unit Stripe requires for the
     * currency, so the result is correct regardless of how the merchant has
     * configured `Currency::decimal_places`.
     *
     * @see https://docs.stripe.com/currencies
     */
    public static function toStripeAmount(int $value, CurrencyContract $currency): int
    {
        return self::rescale($value, max($currency->decimal_places, 0), self::stripeDecimalPlaces($currency));
    }

    /**
     * Convert an amount received from Stripe back to a Lunar price value,
     * scaled by `Currency::decimal_places`. Inverse of `toStripeAmount()`.
     */
    public static function fromStripeAmount(int $amount, CurrencyContract $currency): int
    {
        return self::rescale($amount, self::stripeDecimalPlaces($currency), max($currency->decimal_places, 0));
    }

    /**
     * The number of decimal places Stripe expects amounts in for a currency.
     */
    protected static function stripeDecimalPlaces(CurrencyContract $currency): int
    {
        $code = strtolower($currency->code);

        // UGX is also in the zero-decimal list; the special case takes precedence.
        if (in_array($code, self::SPECIAL_ZERO_DECIMAL_CURRENCIES, true)) {
            return 2;
        }

        if (in_array($code, self::ZERO_DECIMAL_CURRENCIES, true)) {
            return 0;
        }

        if (in_array($code, self::THREE_DECIMAL_CURRENCIES, true)) {
            return 3;
        }

        return 2;
    }

    /**
     * Rescale an integer amount between decimal-place precisions using integer
     * arithmetic only — float division misrounds at half-unit boundaries
     * (145 at 3dp: 0.145 stores as 0.1449…, so round() gives 14, not 15).
     * Rounds half away from zero, matching round().
     */
    protected static function rescale(int $value, int $fromDecimalPlaces, int $toDecimalPlaces): int
    {
        $exponent = $toDecimalPlaces - $fromDecimalPlaces;

        if ($exponent >= 0) {
            return $value * (10 ** $exponent);
        }

        $divisor = 10 ** (-$exponent);

        return intdiv(abs($value) + intdiv($divisor, 2), $divisor) * ($value < 0 ? -1 : 1);
    }

    /**
     * Build the intent
     */
    protected function buildIntent(int $value, string $currencyCode, array $opts = []): PaymentIntent
    {
        $params = [
            'amount' => $value,
            'currency' => $currencyCode,
            'automatic_payment_methods' => ['enabled' => true],
            'capture_method' => config('lunar.stripe.policy', 'automatic'),
        ];

        return PaymentIntent::create([
            ...$params,
            ...$opts,
        ]);
    }
}
