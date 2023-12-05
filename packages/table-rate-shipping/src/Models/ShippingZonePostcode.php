<?php

namespace Lunar\Shipping\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Lunar\Base\BaseModel;
use Lunar\Shipping\Facades\Shipping;
use Lunar\Shipping\Factories\ShippingZonePostcodeFactory;

class ShippingZonePostcode extends BaseModel
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
    protected static function newFactory(): ShippingZonePostcodeFactory
    {
        return ShippingZonePostcodeFactory::new();
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
     * Setter for postcode attribute.
     *
     * @param  string  $value
     * @return void
     */
    public function setPostcodeAttribute($value)
    {
        $this->attributes['postcode'] = str_replace(' ', '', $value);
    }
}
