<?php

namespace Lunar\Shipping\Drivers\ShippingMethods;

use Lunar\DataTypes\ShippingOption;
use Lunar\Facades\Pricing;
use Lunar\Models\Product;
use Lunar\Shipping\DataTransferObjects\ShippingOptionRequest;
use Lunar\Shipping\Interfaces\ShippingRateInterface;
use Lunar\Shipping\Models\ShippingRate;

class ShipBy implements ShippingRateInterface
{
    /**
     * The shipping rate for context.
     */
    public ShippingRate $shippingRate;

    /**
     * {@inheritdoc}
     */
    public function name(): string
    {
        return 'Ship By';
    }

    /**
     * {@inheritdoc}
     */
    public function description(): string
    {
        return 'Offer a set price to ship per order total or per line total.';
    }

    public function resolve(ShippingOptionRequest $shippingOptionRequest): ?ShippingOption
    {
        $shippingRate = $shippingOptionRequest->shippingRate;
        $shippingMethod = $shippingRate->shippingMethod;
        $shippingZone = $shippingRate->shippingZone;
        $data = $shippingMethod->data;
        $cart = $shippingOptionRequest->cart;
        $customerGroups = collect([]);

        if ($user = $cart->user) {
            $customerGroups = $user->customers->pluck('customerGroups')->flatten();
        }

        $subTotal = $cart->lines->sum('subTotal.value');

        // Do we have any products in our exclusions list?
        // If so, we do not want to return this option regardless.
        $productIds = $cart->lines->load('purchasable')->pluck('purchasable.product_id');

        $hasExclusions = $shippingZone->shippingExclusions()
            ->whereHas('exclusions', function ($query) use ($productIds) {
                $query->wherePurchasableType(Product::class)->whereIn('purchasable_id', $productIds);
            })->exists();

        if ($hasExclusions) {
            return null;
        }

        $chargeBy = $data['charge_by'] ?? null;

        if (! $chargeBy) {
            $chargeBy = 'cart_total';
        }

        $tier = $subTotal;

        if ($chargeBy == 'weight') {
            $tier = $cart->lines->sum(function ($line) {
                return $line->purchasable->weight_value * $line->quantity;
            });
        }

        // Do we have a suitable tier price?
        $pricing = Pricing::for($shippingRate)->customerGroups($customerGroups)->qty($tier)->get();

        $prices = $pricing->priceBreaks;

        // If there are customer group prices, they need to take priority.
        if (! $pricing->customerGroupPrices->isEmpty()) {
            $prices = $pricing->customerGroupPrices;
        }

        $matched = $prices->filter(function ($price) use ($tier) {
            return $tier >= $price->min_quantity;
        })->sortByDesc('min_quantity')->first() ?: $pricing->base;

        if (! $matched) {
            return null;
        }

        $price = $matched->price;

        return new ShippingOption(
            name: $shippingMethod->name,
            description: $shippingMethod->description,
            identifier: $shippingRate->getIdentifier(),
            price: $price,
            taxClass: $shippingRate->getTaxClass(),
            taxReference: $shippingRate->getTaxReference(),
            option: $shippingZone->name,
            meta: ['shipping_zone' => $shippingZone->name]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function on(ShippingRate $shippingRate): self
    {
        $this->shippingRate = $shippingRate;

        return $this;
    }
}
