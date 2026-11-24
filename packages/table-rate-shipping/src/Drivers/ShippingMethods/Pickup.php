<?php

namespace Lunar\Shipping\Drivers\ShippingMethods;

use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Product;
use Lunar\Shipping\DataTransferObjects\ShippingOptionRequest;
use Lunar\Shipping\Interfaces\ShippingRateInterface;
use Lunar\Shipping\Models\ShippingRate;

class Pickup implements ShippingRateInterface
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
        return 'Pickup';
    }

    /**
     * {@inheritdoc}
     */
    public function description(): string
    {
        return 'Allow customers to pick up their orders in store';
    }

    public function resolve(ShippingOptionRequest $shippingOptionRequest): ?ShippingOption
    {
        $shippingRate = $shippingOptionRequest->shippingRate;
        $shippingMethod = $shippingRate->shippingMethod;
        $shippingZone = $shippingRate->shippingZone;
        $cart = $shippingOptionRequest->cart;

        // Do we have any products in our exclusions list?
        // If so, we do not want to return this option regardless.
        $productIds = $cart->lines->load('purchasable')->pluck('purchasable.product_id');

        $hasExclusions = $shippingZone->shippingExclusions()
            ->whereHas('exclusions', function ($query) use ($productIds) {
                $query->wherePurchasableType(Product::morphName())
                    ->whereIn('purchasable_id', $productIds);
            })->exists();

        if ($hasExclusions) {
            return null;
        }

        // A pickup option is one with a location that accepts pickup, so without one there is nothing to offer.
        $pickupLocation = $shippingMethod->pickup_location_id ? $shippingMethod->pickupLocation : Location::defaultPickup();

        if (! $pickupLocation?->accepts_pickup) {
            return null;
        }

        return new ShippingOption(
            name: $shippingMethod->name,
            description: $shippingMethod->description,
            identifier: $shippingRate->getIdentifier(),
            price: new PriceValue(
                value: 0,
                currency: $cart->currency,
            ),
            taxClass: $shippingRate->getTaxClass(),
            taxReference: $shippingRate->getTaxReference(),
            option: $shippingZone->name,
            pickupLocation: $pickupLocation,
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
