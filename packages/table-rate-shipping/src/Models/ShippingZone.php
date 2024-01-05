<?php

namespace Lunar\Shipping\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Lunar\Base\BaseModel;
use Lunar\Models\Country;
use Lunar\Models\State;
use Lunar\Shipping\Database\Factories\ShippingZoneFactory;

class ShippingZone extends BaseModel
{
    use HasFactory;

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

    /**
     * Return the shipping methods relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shippingMethods()
    {
        return $this->hasMany(ShippingMethod::class);
    }

    /**
     * Return the countries relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function countries()
    {
        return $this->belongsToMany(
            Country::class,
            config('lunar.database.table_prefix').'country_shipping_zone'
        )->withTimestamps();
    }

    /**
     * Return the states relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function states()
    {
        return $this->belongsToMany(
            State::class,
            config('lunar.database.table_prefix').'state_shipping_zone'
        )->withTimestamps();
    }

    /**
     * Return the postcodes relationship.
     *
     * @return HasMany
     */
    public function postcodes()
    {
        return $this->hasMany(ShippingZonePostcode::class);
    }

    public function rates()
    {
        return $this->hasMany(ShippingRate::class);
    }
}
