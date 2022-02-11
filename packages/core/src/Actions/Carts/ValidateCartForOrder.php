<?php

namespace GetCandy\Actions\Carts;

use GetCandy\Exceptions\Carts\BillingAddressIncompleteException;
use GetCandy\Exceptions\Carts\BillingAddressMissingException;
use GetCandy\Exceptions\Carts\OrderExistsException;
use GetCandy\Exceptions\Carts\ShippingAddressIncompleteException;
use GetCandy\Exceptions\Carts\ShippingAddressMissingException;
use GetCandy\Exceptions\Carts\ShippingOptionMissingException;
use GetCandy\Models\Cart;
use Illuminate\Support\Facades\Validator;

class ValidateCartForOrder
{
    /**
     * Execute the action.
     *
     * @param  \GetCandy\Models\Cart  $cart
     * @return void
     */
    public function execute(
        Cart $cart
    ) {

        // Does this cart already have an order?
        if ($cart->order) {
            throw new OrderExistsException(
                _('getcandy::exceptions.carts.order_exists')
            );
        }

        // Do we have a billing address?
        if (! $cart->billingAddress) {
            throw new BillingAddressMissingException(
                __('getcandy::exceptions.carts.billing_missing')
            );
        }

        $billingValidator = Validator::make(
            $cart->billingAddress->toArray(),
            $this->getAddressRules()
        );

        if ($billingValidator->fails()) {
            throw new BillingAddressIncompleteException();
        }

        // Is this cart shippable and if so, does it have a shipping address.
        if ($cart->getManager()->isShippable()) {
            if (! $cart->shippingAddress) {
                throw new ShippingAddressMissingException(
                    __('getcandy::exceptions.carts.shipping_missing')
                );
            }

            $shippingValidator = Validator::make(
                $cart->shippingAddress->toArray(),
                $this->getAddressRules()
            );

            if ($shippingValidator->fails()) {
                throw new ShippingAddressIncompleteException();
            }

            // Do we have a shipping option applied?
            if (! $cart->getManager()->getShippingOption()) {
                throw new ShippingOptionMissingException();
            }
        }
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
            'line_one'   => 'required',
            'city'       => 'required',
            'postcode'   => 'required',
        ];
    }
}
