<?php

namespace GetCandy\Managers;

use GetCandy\Base\DataTransferObjects\PricingResponse;
use GetCandy\Base\PricingManagerInterface;
use GetCandy\Base\Purchasable;
use GetCandy\Models\Currency;
use GetCandy\Models\CustomerGroup;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

class PricingManager implements PricingManagerInterface
{
    /**
     * The instance of the user
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    protected ?Authenticatable $user = null;

    /**
     * The instance of the currency.
     *
     * @var \GetCandy\Models\Currency
     */
    protected ?Currency $currency = null;

    /**
     * The quantity value.
     *
     * @var integer
     */
    protected int $qty = 1;

    /**
     * The instance of the purchasable object.
     *
     * @var \GetCandy\Base\Purchasable
     */
    protected Purchasable $purchasable;

    /**
     * The customer groups to check against.
     *
     * @var \Illuminate\Support\Collection
     */
    protected ?Collection $customerGroups = null;

    /**
     * Set the user property.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @return self
     */
    public function user(Authenticatable $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Set the currency property.
     *
     * @param \GetCandy\Models\Currency $currency
     * @return self
     */
    public function currency(Currency $currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * Set the quantity property.
     *
     * @param integer $qty
     * @return self
     */
    public function qty(int $qty)
    {
        $this->qty = $qty;
        return $this;
    }

    /**
     * Set the customer groups.
     *
     * @param Collection $customerGroups
     * @return self
     */
    public function customerGroups(Collection $customerGroups)
    {
        $this->customerGroups = $customerGroups;

        return $this;
    }

    /**
     * Set the customer group.
     *
     * @param CustomerGroup $customerGroup
     * @return self
     */
    public function customerGroup(CustomerGroup $customerGroup)
    {
        $this->customerGroups(
            collect([$customerGroup])
        );

        return $this;
    }

    /**
     * Get the price for a purchasable.
     *
     * @param Purchasable $purchasable
     * @return \GetCandy\Base\DataTransferObjects\PricingResponse
     */
    public function for(Purchasable $purchasable)
    {
        if (!$this->currency) {
            $this->currency = Currency::getDefault();
        }

        if (!$this->customerGroups || !$this->customerGroups->count()) {
            $this->customerGroups = collect(
                CustomerGroup::getDefault()
            );
        }

        // Do we have a user?
        if ($this->user && $this->user->customers->count()) {
            $customers = $this->user->customers;
            $customerGroups = $customers->pluck('customerGroups')->flatten();

            if ($customerGroups->count()) {
                $this->customerGroups = $customerGroups;
            }
        }

        $prices = $purchasable->getPrices()->filter(function ($price) {
            return $price->currency_id == $this->currency->id;
        })->filter(function ($price) {
            // Only fetch prices which have no customer group (available to all) or belong to the customer groups
            // that we are trying to check against.
            return !$price->customer_group_id ||
                $this->customerGroups->pluck('id')->contains($price->customer_group_id);
        })->sortBy('price');

        // Get our base price
        $basePrice = $prices->first(fn($price) => $price->tier == 1 && !$price->customer_group_id);

        // To start, we'll set the matched price to the base price.
        $matched = $basePrice;

        // If we have customer group prices, we should find the cheapest one and send that back.
        $potentialGroupPrice = $prices->filter(function ($price) {
            return !!$price->customer_group_id && $price->tier == 1;
        })->sortBy('price');

        $matched = $potentialGroupPrice->first() ?: $matched;

        // Get all tiers that match for the given quantity. These take priority over the other steps
        // as we could be bulk purchasing.
        $tieredPricing = $prices->filter(function ($price) {
            return $price->tier > 1 && $this->qty >= $price->tier;
        })->sortBy('price');

        $matched = $tieredPricing->first() ?: $matched;

        return new PricingResponse(
            matched: $matched,
            base: $prices->first(fn($price) => $price->tier == 1),
            tiered: $prices->filter(fn($price) => $price->tier > 1),
            customerGroupPrices: $prices->filter(fn($price) => !!$price->customer_group_id)
        );
    }
}
