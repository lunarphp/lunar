<?php

namespace Lunar\Shipping\Resolvers;

use Illuminate\Support\Collection;
use Lunar\Models\Cart;
use Lunar\Models\Country;
use Lunar\Models\State;
use Lunar\Shipping\DataTransferObjects\PostcodeLookup;
use Lunar\Shipping\Facades\Shipping;

class ShippingRateResolver
{
    /**
     * The cart to use when resolving.
     */
    protected Cart $cart;

    /**
     * The country to use when resolving.
     */
    protected ?Country $country = null;

    /**
     * The state to use when resolving.
     */
    protected ?string $state = null;

    /**
     * The postcode to use when resolving.
     */
    protected ?string $postcode = null;

    /**
     * Whether all cart items are in stock
     *
     * @var bool
     */
    protected ?bool $allCartItemsAreInStock = null;

    /**
     * Initialise the resolver.
     */
    public function __construct(Cart $cart = null)
    {
        $this->cart($cart);
    }

    /**
     * Set the cart.
     */
    public function cart(Cart $cart): self
    {
        $this->cart = $cart;

        $shippingMeta = $cart->shippingEstimateMeta;

        $this->allCartItemsAreInStock = ! $this->cart->lines->first(function ($line) {
            return $line->purchasable->stock < $line->quantity;
        });

        if (! empty($shippingMeta)) {
            $this->postcode(
                $shippingMeta['postcode'] ?? null
            );
            $this->country(
                $shippingMeta['country'] ?? null
            );
            $this->state(
                $shippingMeta['state'] ?? null
            );

            return $this;
        }

        if ($shippingAddress = $this->cart->shippingAddress) {
            $this->country(
                $shippingAddress->country
            );
            $this->postcode(
                $shippingAddress->postcode
            );
            $this->state(
                $shippingAddress->state
            );
        }

        return $this;
    }

    /**
     * Set the value for country.
     */
    public function country(Country $country = null): self
    {
        $this->country = $country;

        return $this;
    }

    public function state($state): self
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Set the value for the postcode.
     */
    public function postcode(string $postcode): self
    {
        $this->postcode = $postcode;

        return $this;
    }

    /**
     * Return the shipping methods applicable to the cart.
     */
    public function get(): Collection
    {
        if (! $this->postcode || ! $this->country) {
            return collect();
        }

        $zones = Shipping::zones()->country(
            $this->country
        )->state(
            State::whereName($this->state)->first()
        )->postcode(
            new PostcodeLookup(
                country: $this->country,
                postcode: $this->postcode
            )
        )->get();

        $shippingRates = collect();

        foreach ($zones as $zone) {
            $shippingRates = $zone->rates
                ->reject(function ($rate) {
                    $method = $rate->shippingMethod;

                    if (! $method->cutoff) {
                        return false;
                    }

                    [$h, $m, $s] = explode(':', $method->cutoff);

                    return now()->set('hour', $h)
                        ->set('minute', $m)
                        ->set('second', $s)
                        ->isPast();
                })
                ->reject(function ($rate) {
                    if ($this->allCartItemsAreInStock || ! ($rate->shippingMethod->stock_available ?? false)) {
                        return false;
                    }

                    return true;
                });

            foreach ($shippingRates as $shippingRate) {
                $shippingRates->push(
                    $shippingRate
                );
            }
        }

        return $shippingRates->filter()->unique(function ($rate) {
            return $rate->shippingMethod->code;
        });
    }
}
