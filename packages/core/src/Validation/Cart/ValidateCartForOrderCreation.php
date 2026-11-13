<?php

namespace Lunar\Core\Validation\Cart;

use Illuminate\Support\Facades\Validator;
use Lunar\Core\Contracts\Actions\Fulfilment\ResolvesCartFulfilment;
use Lunar\Core\Contracts\FulfilmentMethod;
use Lunar\Core\Drivers\FulfilmentMethods\Pickup;
use Lunar\Core\Enums\PaymentTerms;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Validation\BaseValidator;

class ValidateCartForOrderCreation extends BaseValidator
{
    public function __construct(
        protected ResolvesCartFulfilment $resolveCartFulfilment,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function validate(): bool
    {
        $cart = $this->parameters['cart'];

        // Does this cart already have an order?
        if ($cart->completedOrder) {
            return $this->fail('cart', __('lunar::exceptions.carts.order_exists'));
        }

        $unavailableLines = $cart->lines->filter(
            fn ($line) => ! $line->purchasable || ! $line->purchasable->isPurchasable()
        );

        if ($unavailableLines->isNotEmpty()) {
            return $this->fail('cart', __('lunar::exceptions.carts.line_unavailable', [
                'identifier' => $unavailableLines->map($this->cartLineLabel(...))->implode(', '),
            ]));
        }

        // Do we have a billing address?
        if (! $cart->billingAddress) {
            return $this->fail('cart', __('lunar::exceptions.carts.billing_missing'));
        }

        $billingValidator = Validator::make(
            $cart->billingAddress->toArray(),
            $this->getAddressRules()
        );

        if ($billingValidator->fails()) {
            return $this->fail('cart', $billingValidator->errors()->getMessages());
        }

        if ($cart->isShippable()) {
            // Do we have a shipping option applied?
            if (! $shippingOption = $cart->getShippingOption()) {
                return $this->fail('cart', __('lunar::exceptions.carts.shipping_option_missing'));
            }

            // A pickup option prices no delivery, so lines explicitly set to be delivered need a delivery option.
            if ($shippingOption->isPickup() && $this->hasExplicitDeliveryLines($cart)) {
                return $this->fail('cart', __('lunar::exceptions.carts.delivery_option_missing'));
            }

            // Is this cart going to be shipped and if so, does it have a shipping address?
            if (! $shippingOption->isPickup()) {
                if (! $cart->shippingAddress) {
                    return $this->fail('cart', __('lunar::exceptions.carts.shipping_missing'));
                }

                $shippingValidator = Validator::make(
                    $cart->shippingAddress->toArray(),
                    $this->getAddressRules()
                );

                if ($shippingValidator->fails()) {
                    return $this->fail('cart', $shippingValidator->errors()->getMessages());
                }
            }
        }

        $fulfilment = $this->resolveCartFulfilment->execute($cart);

        $hasPickup = collect($fulfilment->lines)->contains(fn (?FulfilmentMethod $method) => $method?->getKey() === Pickup::KEY);

        if ($cart->payment_terms === PaymentTerms::OnPickup && ! $hasPickup) {
            return $this->fail('cart', __('lunar::exceptions.carts.payment_terms_pickup_missing'));
        }

        $hasDelivery = collect($fulfilment->lines)->contains(fn (?FulfilmentMethod $method) => $method?->requiresDelivery());

        if ($cart->payment_terms === PaymentTerms::OnDelivery && ! $hasDelivery) {
            return $this->fail('cart', __('lunar::exceptions.carts.payment_terms_delivery_missing'));
        }

        return $this->pass();
    }

    private function hasExplicitDeliveryLines(Cart $cart): bool
    {
        $explicit = $this->resolveCartFulfilment->execute($cart, withShippingOption: false);

        return collect($explicit->lines)->contains(fn (?FulfilmentMethod $method) => $method?->requiresDelivery());
    }

    private function cartLineLabel(CartLine $line): string
    {
        return $line->purchasable?->getIdentifier() ?? "#{$line->id}";
    }

    /**
     * Return the address rules for validation.
     *
     * @return array
     */
    private function getAddressRules()
    {
        return [
            'country_id' => 'required',
            'first_name' => 'required',
            'line_one' => 'required',
            'city' => 'required',
            'postcode' => 'required',
        ];
    }
}
