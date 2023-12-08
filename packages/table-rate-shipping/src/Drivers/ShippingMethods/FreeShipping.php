<?php

namespace Lunar\Shipping\Drivers\ShippingMethods;

use Lunar\DataTypes\Price;
use Lunar\DataTypes\ShippingOption;
use Lunar\Models\Product;
use Lunar\Models\TaxClass;
use Lunar\Shipping\DataTransferObjects\ShippingOptionRequest;
use Lunar\Shipping\Interfaces\ShippingMethodInterface;
use Lunar\Shipping\Models\ShippingMethod;

class FreeShipping implements ShippingMethodInterface
{
    /**
     * The shipping method for context.
     */
    public ShippingMethod $shippingMethod;

    /**
     * {@inheritDoc}
     */
    public function name(): string
    {
        return 'Free Shipping';
    }

    /**
     * {@inheritDoc}
     */
    public function description(): string
    {
        return 'Offer free shipping for your customers';
    }

    public function resolve(ShippingOptionRequest $shippingOptionRequest): ?ShippingOption
    {
        $shippingMethod = $shippingOptionRequest->shippingMethod;
        $data = $shippingMethod->data;
        $cart = $shippingOptionRequest->cart;

        // Do we have any products in our exclusions list?
        // If so, we do not want to return this option regardless.
        $productIds = $cart->lines->load('purchasable')->pluck('purchasable.product_id');

        $hasExclusions = $shippingMethod->shippingExclusions()
            ->whereHas('exclusions', function ($query) use ($productIds) {
                $query->wherePurchasableType(Product::class)->whereIn('purchasable_id', $productIds);
            })->exists();

        if ($hasExclusions) {
            return null;
        }

        $subTotal = $cart->lines->sum('subTotal.value');

        if ($data->use_discount_amount ?? false) {
            $subTotal -= $cart->discountTotal->value;
        }

        if (empty($data)) {
            $minSpend = 0;
        } else {
            if (is_array($data->minimum_spend)) {
                $minSpend = ($data->minimum_spend[$cart->currency->code] ?? null);
            } else {
                $minSpend = ($data->minimum_spend->{$cart->currency->code} ?? null);
            }
        }

        if (is_null($minSpend) || ($minSpend) > $subTotal) {
            return null;
        }

        return new ShippingOption(
            name: $shippingMethod->name,
            description: $shippingMethod->description,
            identifier: $shippingMethod->code,
            price: new Price(0, $cart->currency, 1),
            taxClass: TaxClass::getDefault(),
            option: $shippingMethod->shippingZone->name,
            meta: ['shipping_zone' => $shippingMethod->shippingZone->name]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function on(ShippingMethod $shippingMethod): self
    {
        $this->shippingMethod = $shippingMethod;

        return $this;
    }
}
