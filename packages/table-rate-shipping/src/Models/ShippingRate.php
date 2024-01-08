<?php

namespace Lunar\Shipping\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;
use Lunar\Base\BaseModel;
use Lunar\Base\Purchasable;
use Lunar\Base\Traits\HasPrices;
use Lunar\Models\TaxClass;
use Lunar\Shipping\Database\Factories\ShippingZoneFactory;

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
     *
     * @return \Lunar\Shipping\Factories\ShippingZoneFactory
     */
    protected static function newFactory(): ShippingZoneFactory
    {
        return ShippingZoneFactory::new();
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
        return $this->code;
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
        return $this->name ?: $this->driver()->name();
    }

    /**
     * {@inheritDoc}
     */
    public function getOption()
    {
        return $this->code;
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
        return $this->code;
    }

    public function getThumbnail()
    {
        return null;
    }
}
