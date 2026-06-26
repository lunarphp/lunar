<?php

namespace Lunar\Core\DataTypes;

use Illuminate\Support\Collection;
use Lunar\Core\Contracts\Purchasable;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\TaxClass;

class ShippingOption implements Purchasable
{
    public function __construct(
        public string $name,
        public ?string $description,
        public string $identifier,
        public PriceValue $price,
        public TaxClass $taxClass,
        public ?string $taxReference = null,
        public ?string $option = null,
        public bool $collect = false,
        public ?array $meta = null,
    ) {
        //  ..
    }

    /**
     * Get the price for the purchasable item.
     */
    public function getPrice(): PriceValue
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
        return 'shipping';
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

    /**
     * Return the options for this purchasable
     */
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

    /**
     * {@inheritDoc}
     */
    public function requiresFulfilment(): bool
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

    public function canBeFulfilledAtQuantity(int $quantity): bool
    {
        return true;
    }

    public function getTotalInventory(): int
    {
        return 1;
    }

    public function isPurchasable(): bool
    {
        return true;
    }
}
