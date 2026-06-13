<?php

namespace Lunar\Tests\Core\Stubs;

use Illuminate\Support\Collection;
use Lunar\Core\Contracts\Purchasable;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Contracts\TaxClass as TaxClassContract;
use Lunar\Core\Models\TaxClass;

class TestPurchasable implements Purchasable
{
    public function __construct(
        public $name,
        public $description,
        public $identifier,
        public PriceValue $price,
        public TaxClassContract $taxClass,
        public $taxReference = null,
        public $option = null,
        public bool $collect = false,
        public $meta = null,
    ) {
        //  ..
    }

    /**
     * Get the price for the purchasable item.
     *
     * @return Price
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Get prices for the purchasable item.
     */
    public function getPrices(): Collection
    {
        return collect([
            $this->price,
        ]);
    }

    /**
     * Return the purchasable unit quantity.
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
        return 'test-purchsable';
    }

    /**
     * Return the name for the purchasable.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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

    public function getOptions(): Collection
    {
        return collect([
            $this->option,
        ]);
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

    public function requiresFulfilment(): bool
    {
        return $this->isShippable();
    }

    /**
     * {@inheritDoc}
     */
    public function getThumbnail()
    {
        return null;
    }

    /**
     * Return whether the purchasable can be fulfilled at a given quantity
     */
    public function canBeFulfilledAtQuantity(int $quantity): bool
    {
        return true;
    }

    /**
     * Returns the total inventory the purchasable has available
     */
    public function getTotalInventory(): int
    {
        return 999;
    }

    public function isPurchasable(): bool
    {
        return true;
    }
}
