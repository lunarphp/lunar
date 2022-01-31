<?php

namespace GetCandy\DataTypes;

use GetCandy\Base\Purchasable;
use GetCandy\Models\TaxClass;
use Illuminate\Support\Collection;

class ShippingOption implements Purchasable
{
    public function __construct(
        public $description,
        public $identifier,
        public Price $price,
        public TaxClass $taxClass,
        public $taxReference = null,
        public $option = null
    ) {
        //  ..
    }

    /**
     * Get the price for the purchasable item.
     *
     * @param int        $quantity
     * @param Collection $customerGroups
     *
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    public function getPrices(): Collection
    {
        return collect([
            $this->price,
        ]);
    }

    /**
     * Return the purchasable unit quantity.
     *
     * @return int
     */
    public function getUnitQuantity(): int
    {
        return 1;
    }

    /**
     * Return the purchasable tax class.
     */
    public function getTaxClass(): TaxClass
    {
        return $this->taxClass;
    }

    /**
     * Return the purchasable tax reference.
     *
     * @return string|null
     */
    public function getTaxReference()
    {
        return $this->taxReference;
    }

    /**
     * Return what type of purchasable this is, i.e. physical,digital,shipping.
     *
     * @return string
     */
    public function getType()
    {
        return 'shipping';
    }

    /**
     * Return the description for the purchasable.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Return the option for this purchasable.
     *
     * @return string|null
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * Return a unique string which identifies the purchasable item.
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Returns whether the purchasable item is shippable.
     *
     * @return bool
     */
    public function isShippable()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getThumbnail()
    {
        return null;
    }
}
