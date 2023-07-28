<?php

namespace Lunar\Actions\Carts;

use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Validator;
use Lunar\Exceptions\Carts\BillingAddressIncompleteException;
use Lunar\Exceptions\Carts\BillingAddressMissingException;
use Lunar\Exceptions\Carts\OrderExistsException;
use Lunar\Exceptions\Carts\ShippingAddressIncompleteException;
use Lunar\Exceptions\Carts\ShippingAddressMissingException;
use Lunar\Exceptions\Carts\ShippingOptionMissingException;
use Lunar\Models\Cart;

class ValidateCartForOrder
{
    /**
     * Execute the action.
     *
     * @return void
     *
     * @throws BillingAddressMissingException
     * @throws BillingAddressIncompleteException
     * @throws OrderExistsException
     * @throws ShippingAddressIncompleteException
     * @throws ShippingAddressMissingException
     * @throws ShippingOptionMissingException
     */
    public function execute(
        Cart $cart
    ) {
        // Does this cart already have an order?
        if ($cart->order) {
            throw new OrderExistsException(
                (new MessageBag)->add('error', _('lunar::exceptions.carts.order_exists'))
            );
        }

        // Do we have a billing address?
        if (! $cart->billingAddress) {
            throw new BillingAddressMissingException(
                (new MessageBag)->add('error', _('lunar::exceptions.carts.billing_missing'))
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
        if ($cart->isShippable()) {
            if (! $cart->shippingAddress) {
                throw new ShippingAddressMissingException(
                    (new MessageBag)->add('error', _('lunar::exceptions.carts.shipping_missing'))
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
            if (! $cart->getShippingOption()) {
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
            'line_one' => 'required',
            'city' => 'required',
            'postcode' => 'required',
        ];
    }
}
