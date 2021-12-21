<?php

namespace GetCandy\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Database\Factories\TaxZoneCountryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaxZoneCountry extends BaseModel
{
    use HasFactory;

    /**
     * Return a new factory instance for the model.
     *
     * @return \GetCandy\Database\Factories\TaxZoneCountryFactory
     */
    protected static function newFactory(): TaxZoneCountryFactory
    {
        return TaxZoneCountryFactory::new();
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
     * Return the country relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
