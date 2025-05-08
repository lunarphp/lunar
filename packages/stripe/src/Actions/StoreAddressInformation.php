<?php

namespace Lunar\Stripe\Actions;

use Lunar\Models\Contracts\Order as OrderContract;
use Lunar\Models\Country;
use Lunar\Models\Order;
use Lunar\Models\OrderAddress;
use Lunar\Stripe\Facades\Stripe;
use Stripe\PaymentIntent;

class StoreAddressInformation
{
    public function store(OrderContract $order, PaymentIntent $paymentIntent)
    {
        /** @var Order $order */
        $billingAddress = $order->billingAddress ?: new OrderAddress([
            'order_id' => $order->id,
            'type' => 'billing',
        ]);

        $shippingAddress = $order->shippingAddress ?: new OrderAddress([
            'order_id' => $order->id,
            'type' => 'shipping',
        ]);

        $paymentMethod = Stripe::getPaymentMethod($paymentIntent->payment_method);

        if ($paymentIntent->shipping && $stripeShipping = optional($paymentIntent->shipping)->address) {
            $country = Country::where('iso2', $stripeShipping->country)->first();
            $shippingAddress->first_name = optional($paymentIntent->shipping)->name;
            $shippingAddress->last_name = null;
            $shippingAddress->line_one = $stripeShipping->line1;
            $shippingAddress->line_two = $stripeShipping->line2;
            $shippingAddress->city = $stripeShipping->city;
            $shippingAddress->state = $stripeShipping->state;
            $shippingAddress->postcode = $stripeShipping->postal_code;
            $shippingAddress->country_id = $country?->id;
            $shippingAddress->contact_phone = optional($paymentIntent->shipping)->phone;
            $shippingAddress->save();
        }

        if ($paymentMethod && $stripeBilling = $paymentMethod->billing_details?->address) {
            $countryCode = $stripeBilling->country;

            /**
             * Determine whether this was a link customer, as we won't get the full address back, but we can
             * still get the country of origin, which is the minimum we should have.
             */
            if (! $countryCode && $paymentMethod->type == 'link') {
                $charge = Stripe::getCharge($paymentIntent->latest_charge);
                $countryCode = $charge->payment_method_details->link?->country;
            }

            $country = Country::where('iso2', $countryCode)->first();

            $billingAddress->first_name = $paymentMethod->billing_details?->name;
            $billingAddress->last_name = null;
            $billingAddress->line_one = $stripeBilling->line1;
            $billingAddress->line_two = $stripeBilling->line2;
            $billingAddress->city = $stripeBilling->city;
            $billingAddress->state = $stripeBilling->state;
            $billingAddress->postcode = $stripeBilling->postal_code;
            // As a last resort, fallback to the shipping country.
            $billingAddress->country_id = $country ? $country->id : $shippingAddress->country_id;
            $billingAddress->contact_phone = $paymentMethod->billing_details?->phone;
            $billingAddress->contact_email = $paymentMethod->billing_details->email;
            $billingAddress->save();
        }
    }
}
