<?php

namespace Lunar\Shipping\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;
use Lunar\Base\BaseModel;
use Lunar\Base\Purchasable;
use Lunar\Base\Traits\HasPrices;
use Lunar\DataTypes\ShippingOption;
use Lunar\Models\Cart;
use Lunar\Models\TaxClass;
use Lunar\Shipping\Database\Factories\ShippingMethodFactory;
use Lunar\Shipping\DataTransferObjects\ShippingOptionRequest;
use Lunar\Shipping\Facades\Shipping;

class ShippingMethod extends BaseModel implements Purchasable
{
    use HasFactory, HasPrices;

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    protected $casts = [
        'data' => AsArrayObject::class,
    ];

    /**
     * Return a new factory instance for the model.
     *
     * @return \Lunar\Shipping\Factories\ShippingMethodFactory
     */
    protected static function newFactory(): ShippingMethodFactory
    {
        return ShippingMethodFactory::new();
    }

    /**
     * Return the shipping zone relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shippingZone()
    {
        return $this->belongsTo(ShippingZone::class);
    }

    /**
     * Return the shipping method driver.
     */
    public function getShippingOption(Cart $cart): ?ShippingOption
    {
        return $this->driver()->resolve(
            new ShippingOptionRequest(
                cart: $cart,
                shippingMethod: $this
            )
        );
    }

    public function driver()
    {
        return Shipping::driver($this->driver);
    }

    /**
     * Return the shipping exclusions property.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shippingExclusions()
    {
        return $this->belongsToMany(
            ShippingExclusionList::class,
            config('lunar.database.table_prefix').'exclusion_list_shipping_method',
            'method_id',
            'exclusion_id',
            // 'method_id',
        )->withTimestamps();
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
