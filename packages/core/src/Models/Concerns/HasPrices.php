<?php

namespace Lunar\Core\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Lunar\Core\DataObjects\StorefrontContext;
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
     * Return a PricingManager for this model, optionally primed with a
     * storefront context (currency and customer groups) for browse-time
     * price display away from a cart.
     */
    public function pricing(?StorefrontContext $context = null): PricingManager
    {
        $manager = Pricing::for($this);

        return $context ? $manager->using($context) : $manager;
    }
}
