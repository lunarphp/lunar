<?php

namespace Lunar\Validation\Cart;

use Illuminate\Support\Facades\Validator;
use Lunar\Validation\BaseValidator;

class ValidateCartForOrderCreation extends BaseValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate(): bool
    {
        $cart = $this->parameters['cart'];

        // Does this cart already have an order?
        if ($cart->order) {
            return $this->fail('cart', __('lunar::exceptions.carts.order_exists'));
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

        // Is this cart shippable and if so, does it have a shipping address.
        if ($cart->isShippable()) {
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

            // Do we have a shipping option applied?
            if (! $cart->getShippingOption()) {
                return $this->fail('cart', 'Missing Shipping Option');
            }
        }

        return $this->pass();
//         $cart = $this->parameters['cart'] ?? null;
//
//         if (!$cart) {
//             return $this->fail('cart', 'Unable to set shipping option on null');
//         }
//
//         if (! $cart->shippingAddress) {
//             return $this->fail('cart', 'No shipping address on cart');
//         }
//
//         return $this->pass();
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
