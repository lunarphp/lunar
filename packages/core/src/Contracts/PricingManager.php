<?php

namespace Lunar\Core\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Lunar\Core\DataObjects\PricingResponse;
use Lunar\Core\DataObjects\StorefrontContext;
use Lunar\Core\Models\Contracts\Currency;
use Lunar\Core\Models\Contracts\CustomerGroup;

interface PricingManager
{
    /**
     * Set the user property.
     *
     * @return self
     */
    public function user(Authenticatable $user);

    /**
     * Set the currency property.
     *
     * @return self
     */
    public function currency(Currency $currency);

    /**
     * Set the quantity property.
     *
     * @return self
     */
    public function qty(int $qty);

    /**
     * Set the customer groups.
     *
     * @return self
     */
    public function customerGroups(Collection $customerGroups);

    /**
     * Set the customer group.
     *
     * @return self
     */
    public function customerGroup(CustomerGroup $customerGroup);

    /**
     * Apply a resolved storefront context (currency and customer groups).
     *
     * @return self
     */
    public function using(StorefrontContext $context);

    /**
     * Get the price for a purchasable.
     *
     * @return PricingResponse
     */
    public function for(Purchasable $purchasable);
}
