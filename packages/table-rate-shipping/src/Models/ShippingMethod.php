<?php

namespace Lunar\Shipping\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Lunar\Base\BaseModel;
use Lunar\Shipping\Database\Factories\ShippingMethodFactory;
use Lunar\Shipping\Facades\Shipping;
use Lunar\Shipping\Interfaces\ShippingRateInterface;

class ShippingMethod extends BaseModel implements Contracts\ShippingMethod
{
    use HasFactory;

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
     */
    protected static function newFactory()
    {
        return ShippingMethodFactory::new();
    }

    public function shippingRates(): HasMany
    {
        return $this->hasMany(ShippingRate::modelClass());
    }

    public function driver(): ShippingRateInterface
    {
        return Shipping::driver($this->driver);
    }
}
