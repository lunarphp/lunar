<?php

namespace GetCandy\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Base\Traits\HasMacros;
use GetCandy\Database\Factories\TaxRateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaxRate extends BaseModel
{
    use HasFactory;
    use HasMacros;

    /**
     * Return a new factory instance for the model.
     *
     * @return \GetCandy\Database\Factories\TaxRateFactory
     */
    protected static function newFactory(): TaxRateFactory
    {
        return TaxRateFactory::new();
    }

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Return the tax zone relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function taxZone()
    {
        return $this->belongsTo(TaxZone::class);
    }

    /**
     * Return the tax rate amounts relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function taxRateAmounts()
    {
        return $this->hasMany(TaxRateAmount::class);
    }
}
