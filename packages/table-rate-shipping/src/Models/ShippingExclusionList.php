<?php

namespace Lunar\Shipping\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Lunar\Base\BaseModel;
use Lunar\Shipping\Factories\ShippingExclusionListFactory;

class ShippingExclusionList extends BaseModel
{
    use HasFactory;

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    protected $casts = [];

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory(): ShippingExclusionListFactory
    {
        return ShippingExclusionListFactory::new();
    }

    protected static function booted()
    {
        static::deleting(function (ShippingExclusionList $list) {
            $list->exclusions()->delete();
            $list->shippingZones()->detach();
        });
    }

    /**
     * Return the shipping zone relationship.
     */
    public function exclusions(): HasMany
    {
        return $this->hasMany(ShippingExclusion::class);
    }

    /**
     * Return the shipping methods relationship.
     */
    public function shippingZones(): BelongsToMany
    {
        return $this->belongsToMany(
            ShippingZone::class,
            config('lunar.database.table_prefix').'exclusion_list_shipping_zone',
            'exclusion_id',
            'shipping_zone_id',
        );
    }
}
