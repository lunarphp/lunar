<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Lunar\Base\BaseModel;
use Lunar\Database\Factories\DiscountPurchasableFactory;

class DiscountCollection extends BaseModel
{
    use HasFactory;

    /**
     * Define which attributes should be cast.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * {@inheritDoc}
     */
    protected $guarded = [];

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory(): DiscountPurchasableFactory
    {
        return DiscountPurchasableFactory::new();
    }

    /**
     * Return the discount relationship.
     */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    /**
     * Return the collection relationship.
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }
}
