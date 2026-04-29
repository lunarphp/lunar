<?php

namespace Lunar\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Lunar\Models\Contracts\TaxZone;

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
     *
     * @param  TaxZone|null  $taxZone
     */
    public function priceExTax(?TaxZone $taxZone = null): \Lunar\DataTypes\Price;

    /**
     * Return the price inclusive of tax.
     *
     * @param  TaxZone|null  $taxZone
     */
    public function priceIncTax(?TaxZone $taxZone = null): int|\Lunar\DataTypes\Price;

    /**
     * Return the compare price inclusive of tax.
     *
     * @param  TaxZone|null  $taxZone
     */
    public function comparePriceIncTax(?TaxZone $taxZone = null): int|\Lunar\DataTypes\Price;
}
