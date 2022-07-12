<?php

namespace GetCandy\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Base\Casts\Price as CastsPrice;
use GetCandy\Base\Traits\HasMacros;
use GetCandy\Database\Factories\PriceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Price extends BaseModel
{
    use HasFactory;
    use HasMacros;

    /**
     * Return a new factory instance for the model.
     *
     * @return \GetCandy\Database\Factories\PriceFactory
     */
    protected static function newFactory(): PriceFactory
    {
        return PriceFactory::new();
    }

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    protected $casts = [
        'price'         => CastsPrice::class,
        'compare_price' => CastsPrice::class,
    ];

    /**
     * Return the priceable relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function priceable()
    {
        return $this->morphTo();
    }

    /**
     * Return the currency relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Return the customer group relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customerGroup()
    {
        return $this->belongsTo(CustomerGroup::class);
    }
}
