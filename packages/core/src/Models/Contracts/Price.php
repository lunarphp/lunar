<?php

namespace Lunar\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

interface Price
{
    /**
     * Return the priceable relationship.
     */
    public function priceable(): MorphTo;

    /**
     * Return the currency relationship.
     */
    public function currency(): BelongsTo;

    /**
     * Return the customer group relationship.
     */
    public function customerGroup(): BelongsTo;

    /**
     * Return the price exclusive of tax.
     */
    public function priceExTax(?TaxZone $taxZone = null): \Lunar\DataTypes\Price;

    /**
     * Return the price inclusive of tax.
     */
    public function priceIncTax(?TaxZone $taxZone = null): int|\Lunar\DataTypes\Price;

    /**
     * Return the compare price inclusive of tax.
     */
    public function comparePriceIncTax(?TaxZone $taxZone = null): int|\Lunar\DataTypes\Price;
}
