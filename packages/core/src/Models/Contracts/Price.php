<?php

namespace Lunar\Core\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Lunar\Core\DataObjects\PriceValue;

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
    public function priceExTax(?TaxZone $taxZone = null): PriceValue;

    /**
     * Return the price inclusive of tax.
     */
    public function priceIncTax(?TaxZone $taxZone = null): PriceValue;

    /**
     * Return the compare price inclusive of tax.
     */
    public function comparePriceIncTax(?TaxZone $taxZone = null): PriceValue;
}
