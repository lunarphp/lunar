<?php

namespace Lunar\Core\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Lunar\Core\DataObjects\PricingResponse;
use Lunar\Core\DataObjects\StorefrontContext;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;

interface PricingManager
{
    /**
     * Set the purchasable to price.
     *
     * @return self
     */
    public function for(Purchasable $purchasable);

    /**
     * Apply a resolved storefront context. It is the single source of
     * pricing state; the other setters adjust it.
     *
     * @return self
     */
    public function using(StorefrontContext $context);

    /**
     * Price for the given customer, deriving its customer groups.
     *
     * @return self
     */
    public function customer(?Customer $customer);

    /**
     * @deprecated Pass a context with using(), or a customer with customer().
     *
     * @return self
     */
    public function user(?Authenticatable $user);

    /**
     * @deprecated Use customer(null).
     *
     * @return self
     */
    public function guest();

    /**
     * Set the currency.
     *
     * @return self
     */
    public function currency(?Currency $currency);

    /**
     * Set the quantity.
     *
     * @return self
     */
    public function qty(int $qty);

    /**
     * Set the customer groups.
     *
     * @return self
     */
    public function customerGroups(?Collection $customerGroups);

    /**
     * Set the customer group.
     *
     * @return self
     */
    public function customerGroup(?CustomerGroup $customerGroup);

    /**
     * The context being priced for — what a pricing pipeline reads the
     * region, customer and channel from.
     */
    public function getContext(): StorefrontContext;

    public function getPurchasable(): Purchasable;

    public function getQuantity(): int;

    /**
     * Get the price for the purchasable.
     *
     * @return PricingResponse
     */
    public function get();
}
