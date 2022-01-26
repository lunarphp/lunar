<?php

namespace GetCandy\Managers;

use GetCandy\Base\DataTransferObjects\PricingResponse;
use GetCandy\Base\PricingManagerInterface;
use GetCandy\Base\Purchasable;
use GetCandy\Models\Currency;
use GetCandy\Models\CustomerGroup;
use Illuminate\Auth\Authenticatable;
use Illuminate\Support\Collection;

class PricingManager implements PricingManagerInterface
{
    /**
     * The instance of the user
     *
     * @var \Illuminate\Auth\Authenticatable
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
     * @param \Illuminate\Auth\Authenticatable $user
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

        $prices = $purchasable->getPrices()->filter(function ($price) {
            return $price->currency_id == $this->currency->id;
        })->filter(function ($price) {
            // If we don't have a user, or customer groups passed through, only
            // return the prices that or either null or associated to the default
            // customer group.
            return !$price->customer_group_id || $this->customerGroups->contains($price->customer_group_id);
        })->sortBy('price');

        return new PricingResponse(
            matched: $prices->first(fn($price) => $price->tier <= $this->qty),
            base: $prices->first(fn($price) => $price->tier == 1),
            tiered: $prices->filter(fn($price) => $price->tier > 1 && $this->qty <= $price->tier),
            customerGroupPrices: $prices->filter(fn($price) => !!$price->customer_group_id)
        );
    }
}
