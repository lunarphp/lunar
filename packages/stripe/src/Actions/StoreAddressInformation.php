<?php

namespace Lunar\Stripe\Actions;

use Lunar\Models\Country;
use Lunar\Models\Order;
use Lunar\Models\OrderAddress;
use Lunar\Stripe\Facades\Stripe;
use Stripe\PaymentIntent;

class StoreAddressInformation
{
    public function store(Order $order, PaymentIntent $paymentIntent)
    {
        $billingAddress = $order->billingAddress ?: new OrderAddress([
            'order_id' => $order->id,
            'type' => 'billing',
        ]);

        $shippingAddress = $order->shippingAddress ?: new OrderAddress([
            'order_id' => $order->id,
            'type' => 'shipping',
        ]);

        $paymentMethod = Stripe::getPaymentMethod($paymentIntent->payment_method);

        if ($paymentIntent->shipping && $stripeShipping = $paymentIntent->shipping->address) {
            $country = Country::where('iso2', $stripeShipping->country)->first();
            $shippingAddress->first_name = explode(' ', $paymentIntent->shipping->name)[0];
            $shippingAddress->last_name = explode(' ', $paymentIntent->shipping->name)[1] ?? '';
            $shippingAddress->line_one = $stripeShipping->line1;
            $shippingAddress->line_two = $stripeShipping->line2;
            $shippingAddress->city = $stripeShipping->city;
            $shippingAddress->state = $stripeShipping->state;
            $shippingAddress->postcode = $stripeShipping->postal_code;
            $shippingAddress->country_id = $country->id;
            $shippingAddress->contact_phone = $paymentIntent->shipping->phone;
            $shippingAddress->save();
        }

        if ($paymentMethod && $stripeBilling = $paymentMethod->billing_details?->address) {
            $country = Country::where('iso2', $stripeBilling->country)->first();
            $billingAddress->first_name = explode(' ', $paymentMethod->billing_details->name)[0];
            $billingAddress->last_name = explode(' ', $paymentMethod->billing_details->name)[1] ?? '';
            $billingAddress->line_one = $stripeBilling->line1;
            $billingAddress->line_two = $stripeBilling->line2;
            $billingAddress->city = $stripeBilling->city;
            $billingAddress->state = $stripeBilling->state;
            $billingAddress->postcode = $stripeBilling->postal_code;
            $billingAddress->country_id = $country->id;
            $billingAddress->contact_phone = $paymentMethod->billing_details->phone;
            $billingAddress->contact_email = $paymentMethod->billing_details->email;
            $billingAddress->save();
        }
    }
}
