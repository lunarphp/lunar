<?php

namespace Lunar\Shipping\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Lunar\Base\BaseModel;
use Lunar\Shipping\Facades\Shipping;
use Lunar\Shipping\Factories\ShippingExclusionFactory;

class ShippingExclusion extends BaseModel
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
    protected static function newFactory(): ShippingExclusionFactory
    {
        return ShippingExclusionFactory::new();
    }

    /**
     * Return the shipping zone relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function list()
    {
        return $this->belongsTo(ShippingZone::class);
    }

    /**
     * Return the purchasable relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function purchasable()
    {
        return $this->morphTo('purchasable');
    }
}
