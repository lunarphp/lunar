<?php

namespace Lunar\Stripe\Managers;

use Illuminate\Support\Collection;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Stripe\Enums\CancellationReason;
use Stripe\Charge;
use Stripe\Exception\InvalidRequestException;
use Stripe\PaymentIntent;
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

    /**
     * Create a payment intent from a Cart
     */
    public function createIntent(Cart $cart, array $opts = []): PaymentIntent
    {
        $meta = (array) $cart->meta;

        if ($meta && ! empty($meta['payment_intent'])) {
            $intent = $this->fetchIntent(
                $meta['payment_intent']
            );

            if ($intent) {
                return $intent;
            }
        }

        $paymentIntent = $this->buildIntent(
            $cart->total->value,
            $cart->currency->code,
            $cart->shippingAddress,
            $opts
        );

        if (! $meta) {
            $cart->update([
                'meta' => [
                    'payment_intent' => $paymentIntent->id,
                ],
            ]);
        } else {
            $meta['payment_intent'] = $paymentIntent->id;
            $cart->meta = $meta;
            $cart->save();
        }

        return $paymentIntent;
    }

    public function updateShippingAddress(Cart $cart): void
    {
        $address = $cart->shippingAddress;

        if (! $address) {
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

    public function updateIntent(Cart $cart, array $values): void
    {
        $meta = (array) $cart->meta;

        if (empty($meta['payment_intent'])) {
            return;
        }

        $this->updateIntentById($meta['payment_intent'], $values);
    }

    public function updateIntentById(string $id, array $values): void
    {
        $this->getClient()->paymentIntents->update(
            $id,
            $values
        );
    }

    public function syncIntent(Cart $cart): void
    {
        $meta = (array) $cart->meta;

        if (empty($meta['payment_intent'])) {
            return;
        }

        $cart = $cart->calculate();

        $this->getClient()->paymentIntents->update(
            $meta['payment_intent'],
            ['amount' => $cart->total->value]
        );
    }

    public function cancelIntent(Cart $cart, CancellationReason $reason): void
    {
        $meta = (array) $cart->meta;

        if (empty($meta['payment_intent'])) {
            return;
        }

        try {
            $this->getClient()->paymentIntents->cancel(
                $meta['payment_intent'],
                ['cancellation_reason' => $reason->value]
            );
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
     * Build the intent
     */
    protected function buildIntent(int $value, string $currencyCode, ?CartAddress $shipping, array $opts = []): PaymentIntent
    {
        $params = [
            'amount' => $value,
            'currency' => $currencyCode,
            'automatic_payment_methods' => ['enabled' => true],
            'capture_method' => config('lunar.stripe.policy', 'automatic'),
        ];

        if ($shipping) {
            $params['shipping'] = [
                'name' => "{$shipping->first_name} {$shipping->last_name}",
                'phone' => $shipping->contact_phone,
                'address' => [
                    'city' => $shipping->city,
                    'country' => $shipping->country->iso2,
                    'line1' => $shipping->line_one,
                    'line2' => $shipping->line_two,
                    'postal_code' => $shipping->postcode,
                    'state' => $shipping->state,
                ],
            ];
        }

        return PaymentIntent::create([
            ...$params,
            ...$opts,
        ]);
    }
}
