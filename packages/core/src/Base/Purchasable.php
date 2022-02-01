<?php

namespace GetCandy\Base;

use GetCandy\Models\TaxClass;
use Illuminate\Support\Collection;

interface Purchasable
{
    /**
     * Get the purchasable prices.
     *
     * @return \Illuminate\Support\Collection<\GetCandy\Models\Price>
     */
    public function getPrices(): Collection;

    /**
     * Return the purchasable unit quantity.
     *
     * @return int
     */
    public function getUnitQuantity(): int;

    /**
     * Return the purchasable tax class.
     */
    public function getTaxClass(): TaxClass;

    /**
     * Return the purchasable tax reference.
     *
     * @return string|null
     */
    public function getTaxReference();

    /**
     * Return what type of purchasable this is, i.e. physical,digital,shipping.
     *
     * @return string
     */
    public function getType();

    /**
     * Return the description for the purchasable.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Return the option for this purchasable.
     *
     * @return string|null
     */
    public function getOption();

    /**
     * Return a unique string which identifies the purchasable item.
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Returns whether the purchasable item is shippable.
     *
     * @return bool
     */
    public function isShippable();

    /**
     * Return the thumbnail for the purchasable item.
     *
     * @return string
     */
    public function getThumbnail();
}
