<?php

namespace GetCandy\Base\Traits;

use GetCandy\Facades\Pricing;
use GetCandy\Models\Price;

trait HasPrices
{
    /**
     * Get all of the models prices.
     */
    public function prices()
    {
        return $this->morphMany(
            Price::class,
            'priceable'
        );
    }

    /**
     * Return base prices query.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function basePrices()
    {
        return $this->prices()->whereTier(1)->whereNull('customer_group_id');
    }

    /**
     * Return a PricingManager for this model.
     *
     * @return \GetCandy\Managers\PricingManager
     */
    public function pricing()
    {
        return Pricing::for($this);
    }
}
