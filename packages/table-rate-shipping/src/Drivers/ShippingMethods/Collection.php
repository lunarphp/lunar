<?php

namespace Lunar\Shipping\Drivers\ShippingMethods;

use Lunar\DataTypes\Price;
use Lunar\DataTypes\ShippingOption;
use Lunar\Models\Product;
use Lunar\Shipping\DataTransferObjects\ShippingOptionRequest;
use Lunar\Shipping\Http\Livewire\Components\ShippingMethods\Collection as ShippingMethodsCollection;
use Lunar\Shipping\Interfaces\ShippingMethodInterface;
use Lunar\Shipping\Models\ShippingMethod;

class Collection implements ShippingMethodInterface
{
    /**
     * The shipping method for context.
     *
     * @var ShippingMethod
     */
    public ShippingMethod $shippingMethod;

    /**
     * {@inheritdoc}
     */
    public function name(): string
    {
        return 'Collection';
    }

    /**
     * {@inheritdoc}
     */
    public function description(): string
    {
        return 'Allow customers to pick up their orders in store';
    }

    public function resolve(ShippingOptionRequest $shippingOptionRequest): ShippingOption|null
    {
        $shippingMethod = $shippingOptionRequest->shippingMethod;
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

        return new ShippingOption(
            name: $shippingMethod->name,
            description: $shippingMethod->description,
            identifier: $shippingMethod->getIdentifier(),
            price: new Price(
                value: 0,
                currency: $cart->currency,
                unitQty: 1
            ),
            taxClass: $shippingMethod->getTaxClass(),
            taxReference: $shippingMethod->getTaxReference(),
            option: $shippingMethod->shippingZone->name,
            collect: true,
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
