<?php

namespace Lunar\Shipping\Drivers\ShippingMethods;

use Lunar\DataTypes\ShippingOption;
use Lunar\Facades\Pricing;
use Lunar\Models\Product;
use Lunar\Shipping\DataTransferObjects\ShippingOptionRequest;
use Lunar\Shipping\Http\Livewire\Components\ShippingMethods\FlatRate as ShippingMethodsFlatRate;
use Lunar\Shipping\Interfaces\ShippingMethodInterface;
use Lunar\Shipping\Models\ShippingMethod;

class FlatRate implements ShippingMethodInterface
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
        return 'Flat Rate Shipping';
    }

    /**
     * {@inheritdoc}
     */
    public function description(): string
    {
        return 'Offer a set price to ship per order total or per line total.';
    }

    public function resolve(ShippingOptionRequest $shippingOptionRequest): ShippingOption|null
    {
        $data = $shippingOptionRequest->shippingMethod->data;
        $cart = $shippingOptionRequest->cart;
        $shippingMethod = $shippingOptionRequest->shippingMethod;

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

        $pricing = Pricing::for($shippingMethod)->qty($subTotal)->get();

        if (! $pricing->matched) {
            return null;
        }

        return new ShippingOption(
            name: $shippingMethod->name ?: $this->name(),
            description: $shippingMethod->description ?: $this->description(),
            identifier: $shippingMethod->getIdentifier(),
            price: $pricing->matched->price,
            taxClass: $shippingMethod->getTaxClass(),
            taxReference: $shippingMethod->getTaxReference(),
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
