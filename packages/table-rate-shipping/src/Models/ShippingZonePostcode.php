<?php

namespace Lunar\Shipping\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Lunar\Base\BaseModel;
use Lunar\Shipping\Factories\ShippingZonePostcodeFactory;

class ShippingZonePostcode extends BaseModel implements \Lunar\Shipping\Models\Contracts\ShippingZonePostcode
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
    protected static function newFactory()
    {
        return ShippingZonePostcodeFactory::new();
    }

    public function shippingZone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::modelClass());
    }

    /**
     * Setter for postcode attribute.
     */
    public function setPostcodeAttribute(?string $value): void
    {
        $this->attributes['postcode'] = str_replace(' ', '', $value);
    }
}
