<?php

namespace Lunar\Shipping\Resolvers;

use Illuminate\Support\Collection;
use Lunar\Facades\Converter;
use Lunar\Models\Contracts\Cart as CartContract;
use Lunar\Models\Contracts\Country as CountryContract;
use Lunar\Models\CustomerGroup;
use Lunar\Models\State;
use Lunar\Shipping\DataTransferObjects\PostcodeLookup;
use Lunar\Shipping\Facades\Shipping;

class ShippingRateResolver
{
    /**
     * The cart to use when resolving.
     */
    protected CartContract $cart;

    /**
     * The country to use when resolving.
     */
    protected ?CountryContract $country = null;

    /**
     * The customer group to limit to.
     */
    public ?Collection $customerGroups = null;

    /**
     * The state to use when resolving.
     */
    protected ?string $state = null;

    /**
     * The postcode to use when resolving.
     */
    protected ?string $postcode = null;

    /**
     * Whether all cart items are in stock.
     */
    protected ?bool $allCartItemsAreInStock = null;

    /**
     * Total cart weight normalised to kg (the Converter's base weight unit).
     */
    protected float $cartWeightKg = 0.0;

    /**
     * Initialise the resolver.
     */
    public function __construct(?CartContract $cart = null)
    {
        $this->cart($cart);
    }

    /**
     * Set the cart.
     */
    public function cart(CartContract $cart): self
    {
        $this->cart = $cart;

        $shippingMeta = $cart->shippingEstimateMeta;

        $this->allCartItemsAreInStock = ! $this->cart->lines->first(function ($line) {
            return $line->purchasable->shippable && ($line->purchasable->stock < $line->quantity);
        });

        // Sum all line weights converted to kg so we can cheaply convert to any
        // method's weight_unit later without iterating cart lines again.
        $this->cartWeightKg = (float) $this->cart->lines->sum(function ($line) {
            $value = (float) ($line->purchasable->weight_value ?? 0);
            $unit = $line->purchasable->weight_unit ?? 'kg';

            return (float) Converter::from("weight.{$unit}")
                ->to('weight.kg')
                ->value($value * $line->quantity)
                ->convert()
                ->getValue();
        });

        $this->customerGroups = collect([CustomerGroup::getDefault()]);

        if ($user = $this->cart->user) {
            $this->customerGroups = $user->customers->first()?->customerGroups ?: $this->customerGroups;
        }

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
    public function country(?CountryContract $country = null): self
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
    public function postcode(?string $postcode): self
    {
        $this->postcode = $postcode;

        return $this;
    }

    /**
     * Return the cart weight expressed in the given unit.
     */
    protected function cartWeightIn(string $unit): float
    {
        if ($unit === 'kg') {
            return $this->cartWeightKg;
        }

        return (float) Converter::from('weight.kg')
            ->to("weight.{$unit}")
            ->value($this->cartWeightKg)
            ->convert()
            ->getValue();
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
            $zoneShippingRates = $zone->rates
                ->reject(function ($state) {
                    return ! $state->enabled;
                })
                ->reject(function ($rate) {
                    $method = $rate->shippingMethod()->customerGroup($this->customerGroups)->first();

                    return ! $method;
                })
                ->reject(function ($rate) {
                    $method = $rate->shippingMethod;

                    if (! $method) {
                        return true;
                    }

                    return ! $method->isAvailable();
                })
                ->reject(function ($rate) {
                    if ($this->allCartItemsAreInStock || ! ($rate->shippingMethod->stock_available ?? false)) {
                        return false;
                    }

                    return true;
                })
                ->reject(function ($rate) {
                    $method = $rate->shippingMethod;
                    $minWeight = $method->min_weight;
                    $maxWeight = $method->max_weight;

                    if ($minWeight === null && $maxWeight === null) {
                        return false;
                    }

                    $cartWeight = $this->cartWeightIn($method->weight_unit ?? 'kg');

                    if ($minWeight !== null && $cartWeight < (float) $minWeight) {
                        return true;
                    }

                    if ($maxWeight !== null && $cartWeight > (float) $maxWeight) {
                        return true;
                    }

                    return false;
                });

            foreach ($zoneShippingRates as $shippingRate) {
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
