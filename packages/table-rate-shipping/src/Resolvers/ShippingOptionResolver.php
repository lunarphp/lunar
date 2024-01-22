<?php

namespace Lunar\Shipping\Resolvers;

use Illuminate\Support\Collection;
use Lunar\Models\Cart;
use Lunar\Shipping\DataTransferObjects\ShippingOptionLookup;
use Lunar\Shipping\Events\ShippingOptionResolvedEvent;

class ShippingOptionResolver
{
    /**
     * The cart to use when resolving.
     *
     * @var Cart
     */
    protected ?Cart $cart;

    /**
     * Initialise the resolver.
     *
     * @param  Cart  $cart
     */
    public function __construct(Cart $cart = null)
    {
        $this->cart = $cart;
    }

    /**
     * Set the cart.
     */
    public function cart(Cart $cart): self
    {
        $this->cart = $cart;

        return $this;
    }

    /**
     * Return the shipping methods applicable to the cart.
     */
    public function get(ShippingOptionLookup $shippingOptionLookup): Collection
    {
        $shippingOptions = collect();

        if (! $this->cart) {
            return collect();
        }

        foreach ($shippingOptionLookup->shippingRates as $shippingRate) {
            $shippingOptions->push((object) [
                'shippingRate' => $shippingRate,
                'option' => $shippingRate->getShippingOption($this->cart),
            ]);
        }

        return $shippingOptions->filter(function ($option) {
            return (bool) $option->option;
        })->unique(function ($option) {
            return $option->option->getIdentifier();
        })->each(function ($option) {
            ShippingOptionResolvedEvent::dispatch(
                $this->cart,
                $option->shippingRate,
                $option->option
            );
        });
    }
}
