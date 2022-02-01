<?php

namespace GetCandy\Base;

use GetCandy\Models\Currency;
use GetCandy\Models\CustomerGroup;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

interface PricingManagerInterface
{
    /**
     * Set the user property.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return self
     */
    public function user(Authenticatable $user);

    /**
     * Set the currency property.
     *
     * @param  \GetCandy\Models\Currency  $currency
     * @return self
     */
    public function currency(Currency $currency);

    /**
     * Set the quantity property.
     *
     * @param  int  $qty
     * @return self
     */
    public function qty(int $qty);

    /**
     * Set the customer groups.
     *
     * @param  Collection  $customerGroups
     * @return self
     */
    public function customerGroups(Collection $customerGroups);

    /**
     * Set the customer group.
     *
     * @param  CustomerGroup  $customerGroup
     * @return self
     */
    public function customerGroup(CustomerGroup $customerGroup);

    /**
     * Get the price for a purchasable.
     *
     * @param  Purchasable  $purchasable
     * @return \GetCandy\Base\DataTransferObjects\PricingResponse
     */
    public function for(Purchasable $purchasable);
}
