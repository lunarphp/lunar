<?php

namespace Lunar\Stripe\Managers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Lunar\Models\Cart;
use Lunar\Stripe\Enums\CancellationReason;
use Stripe\Charge;
use Stripe\Customer;
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

    public function getCartIntentId(Cart $cart): ?string
    {
        return $cartModel->meta['payment_intent'] ?? $cart->paymentIntents->first()?->intent_id;
    }

    public function fetchOrCreateIntent(Cart $cart, array $createOptions = []): PaymentIntent
    {
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

    public function createCustomerFromModel(Authenticatable $authenticatable): ?Customer
    {
        if (! is_lunar_user($authenticatable)) {
            return $this->createCustomer($authenticatable->name, $authenticatable->email);
        }
        $customer = $authenticatable->customers->first();

        $params = [
            'description' => $customer->company_name,
        ];

        $billingAddress = $customer->addresses()->where('billing_default', true)->first();

        $shippingAddress = $customer->addresses()->where('shipping_default', true)->first() ?: $billingAddress;

        if ($billingAddress) {
            $params['address'] = [
                'line1' => $billingAddress->line_one,
                'line2' => $billingAddress->line_two,
                'city' => $billingAddress->city,
                'state' => $billingAddress->state,
                'postal_code' => $billingAddress->postcode,
                'country' => $billingAddress->country->iso2,
            ];
        }

        if ($shippingAddress) {
            $params['shipping'] = [
                'name' => "{$shippingAddress->first_name} {$shippingAddress->last_name}",
                'address' => [
                    'line1' => $shippingAddress->line_one,
                    'line2' => $shippingAddress->line_two,
                    'city' => $shippingAddress->city,
                    'state' => $shippingAddress->state,
                    'postal_code' => $shippingAddress->postcode,
                    'country' => $shippingAddress->country->iso2,
                ],
            ];
        }

        $customer = $this->createCustomer($authenticatable->name, $authenticatable->email, $params);

        if ($customer) {
            $authenticatable->stripe_id = $customer->id;
            $authenticatable->save();
        }

        return $customer;
    }

    public function createCustomer(string $fullName, string $email, array $params = []): ?Customer
    {
        try {
            return Customer::create([
                'name' => $fullName,
                'email' => $email,
                ...$params,
            ]);
        } catch (ApiErrorException $e) {

        }

        return null;
    }

    /**
     * Create a payment intent from a Cart
     */
    public function createIntent(Cart $cart, array $opts = []): PaymentIntent
    {
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
            $cart->total->value,
            $cart->currency->code,
            $opts
        );

        $cart->paymentIntents()->create([
            'intent_id' => $paymentIntent->id,
            'status' => $paymentIntent->status,
        ]);

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

    public function syncIntent(Cart $cart): void
    {
        $intentId = $this->getCartIntentId($cart);

        if (! $intentId) {
            return;
        }

        $cart = $cart->calculate();

        $this->getClient()->paymentIntents->update(
            $intentId,
            ['amount' => $cart->total->value]
        );
    }

    public function cancelIntent(Cart $cart, CancellationReason $reason): void
    {
        $intentId = $this->getCartIntentId($cart);

        if (! $intentId) {
            return;
        }

        try {
            $this->getClient()->paymentIntents->cancel(
                $intentId,
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
