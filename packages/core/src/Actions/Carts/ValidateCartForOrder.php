<?php

namespace Lunar\Actions\Carts;

use Lunar\Exceptions\Carts\BillingAddressIncompleteException;
use Lunar\Exceptions\Carts\BillingAddressMissingException;
use Lunar\Exceptions\Carts\OrderExistsException;
use Lunar\Exceptions\Carts\ShippingAddressIncompleteException;
use Lunar\Exceptions\Carts\ShippingAddressMissingException;
use Lunar\Exceptions\Carts\ShippingOptionMissingException;
use Lunar\Models\Cart;
use Illuminate\Support\Facades\Validator;

class ValidateCartForOrder
{
    /**
     * Execute the action.
     *
     * @param  \Lunar\Models\Cart  $cart
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
