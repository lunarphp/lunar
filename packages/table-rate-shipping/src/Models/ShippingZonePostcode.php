<?php

namespace Lunar\Shipping\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Lunar\Core\Models\Base;
use Lunar\Shipping\Factories\ShippingZonePostcodeFactory;

class ShippingZonePostcode extends Base implements Contracts\ShippingZonePostcode
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

    /**
     * Return the shipping zone relationship.
     */
    public function shippingZone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class);
    }

    /**
     * Setter for postcode attribute.
     */
    public function setPostcodeAttribute(?string $value): void
    {
        $this->attributes['postcode'] = str_replace(' ', '', $value);
    }
}
