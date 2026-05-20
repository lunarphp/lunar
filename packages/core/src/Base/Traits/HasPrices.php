<?php

namespace Lunar\Core\Base\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Lunar\Core\Facades\Pricing;
use Lunar\Core\Managers\PricingManager;
use Lunar\Core\Models\Price;

trait HasPrices
{
    /**
     * Get all of the models prices.
     */
    public function prices(): MorphMany
    {
        return $this->morphMany(
            Price::modelClass(),
            'priceable'
        );
    }

    /**
     * Return base prices query.
     */
    public function basePrices(): MorphMany
    {
        return $this->prices()->whereMinQuantity(1)->whereNull('customer_group_id');
    }

    public function priceBreaks(): MorphMany
    {
        return $this->prices()->where('min_quantity', '>', 1);
    }

    /**
     * Return a PricingManager for this model.
     */
    public function pricing(): PricingManager
    {
        return Pricing::for($this);
    }
}
