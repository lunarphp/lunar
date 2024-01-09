<?php

namespace Lunar\Shipping\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;
use Lunar\Base\BaseModel;
use Lunar\Base\Purchasable;
use Lunar\Base\Traits\HasPrices;
use Lunar\DataTypes\ShippingOption;
use Lunar\Models\Cart;
use Lunar\Models\TaxClass;
use Lunar\Shipping\Database\Factories\ShippingRateFactory;
use Lunar\Shipping\DataTransferObjects\ShippingOptionRequest;

class ShippingRate extends BaseModel implements Purchasable
{
    use HasFactory;
    use HasPrices;

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory(): ShippingRateFactory
    {
        return ShippingRateFactory::new();
    }

    public function shippingZone()
    {
        return $this->belongsTo(ShippingZone::class);
    }

    public function shippingMethod()
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function getPrices(): Collection
    {
        return $this->prices;
    }

    /**
     * Return the unit quantity for the variant.
     */
    public function getUnitQuantity(): int
    {
        return 1;
    }

    /**
     * Return the tax class.
     */
    public function getTaxClass(): TaxClass
    {
        return TaxClass::getDefault();
    }

    public function getTaxReference()
    {
        return $this->shippingMethod->code;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return 'shipping';
    }

    /**
     * {@inheritDoc}
     */
    public function isShippable()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        return $this->shippingMethod->name ?: $this->driver()->name();
    }

    /**
     * {@inheritDoc}
     */
    public function getOption()
    {
        return $this->shippingMethod->code;
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions()
    {
        return collect();
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifier()
    {
        return $this->shippingMethod->code;
    }

    public function getThumbnail()
    {
        return null;
    }

    /**
     * Return the shipping method driver.
     */
    public function getShippingOption(Cart $cart): ?ShippingOption
    {
        return $this->shippingMethod->driver()->resolve(
            new ShippingOptionRequest(
                shippingRate: $this,
                cart: $cart,
            )
        );
    }
}
