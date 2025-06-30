<?php

namespace Lunar\Shipping\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Lunar\Base\BaseModel;
use Lunar\Base\Traits\HasCustomerGroups;
use Lunar\Models\CustomerGroup;
use Lunar\Shipping\Database\Factories\ShippingMethodFactory;
use Lunar\Shipping\Facades\Shipping;
use Lunar\Shipping\Interfaces\ShippingRateInterface;

class ShippingMethod extends BaseModel implements Contracts\ShippingMethod
{
    use HasCustomerGroups;
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

    protected static function booted()
    {
        static::deleting(function (self $shippingMethod) {
            DB::beginTransaction();
            $shippingMethod->customerGroups()->detach();
            $shippingMethod->shippingRates()->delete();
            DB::commit();
        });
    }

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

    /**
     * Return the customer groups relationship.
     */
    public function customerGroups(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            CustomerGroup::modelClass(),
            "{$prefix}customer_group_shipping_method"
        )->withPivot([
            'visible',
            'enabled',
            'starts_at',
            'ends_at',
        ])->withTimestamps();
    }
}
