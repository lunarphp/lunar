<?php

namespace Lunar\Shipping\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Lunar\Core\Models\Base;
use Lunar\Shipping\Database\Factories\ShippingExclusionFactory;

class ShippingExclusion extends Base
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
        return ShippingExclusionFactory::new();
    }

    /**
     * Return the exclusion list relationship.
     */
    public function list(): BelongsTo
    {
        return $this->belongsTo(ShippingExclusionList::class, 'shipping_exclusion_list_id');
    }

    /**
     * Return the purchasable relationship.
     */
    public function purchasable(): MorphTo
    {
        return $this->morphTo('purchasable');
    }
}
