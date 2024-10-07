<?php

namespace Lunar\Base;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Lunar\Models\Contracts\Currency;
use Lunar\Models\Contracts\CustomerGroup;

interface PricingManagerInterface
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
     * Get the price for a purchasable.
     *
     * @return \Lunar\Base\DataTransferObjects\PricingResponse
     */
    public function for(Purchasable $purchasable);
}
