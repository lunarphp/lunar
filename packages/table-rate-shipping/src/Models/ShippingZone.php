<?php

namespace Lunar\Shipping\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Lunar\Base\BaseModel;
use Lunar\Models\Country;
use Lunar\Models\Order;
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
     */
    protected static function newFactory(): ShippingZoneFactory
    {
        return ShippingZoneFactory::new();
    }

    protected static function booted()
    {
        static::deleting(function (self $shippingZone) {
            DB::beginTransaction();
            $shippingZone->rates()->delete();
            $shippingZone->countries()->detach();
            $shippingZone->orders()->detach();
            $shippingZone->states()->detach();
            $shippingZone->shippingExclusions()->detach();
            $shippingZone->postcodes()->delete();
            DB::commit();
        });
    }

    /**
     * Return the shipping methods relationship.
     */
    public function shippingMethods(): HasMany
    {
        return $this->hasMany(ShippingMethod::class);
    }

    /**
     * Return the countries relationship.
     */
    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(
            Country::class,
            config('lunar.database.table_prefix').'country_shipping_zone'
        )->withTimestamps();
    }

    /**
     * Return the countries relationship.
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(
            Order::class,
            config('lunar.database.table_prefix').'order_shipping_zone'
        )->withTimestamps();
    }

    /**
     * Return the states relationship.
     */
    public function states(): BelongsToMany
    {
        return $this->belongsToMany(
            State::class,
            config('lunar.database.table_prefix').'state_shipping_zone'
        )->withTimestamps();
    }

    /**
     * Return the postcodes relationship.
     */
    public function postcodes(): HasMany
    {
        return $this->hasMany(ShippingZonePostcode::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(ShippingRate::class);
    }

    /**
     * Return the shipping exclusions property.
     */
    public function shippingExclusions(): BelongsToMany
    {
        return $this->belongsToMany(
            ShippingExclusionList::class,
            config('lunar.database.table_prefix').'exclusion_list_shipping_zone',
            'shipping_zone_id',
            'exclusion_id',
        )->withTimestamps();
    }
}
