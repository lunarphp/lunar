<?php

namespace GetCandy\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Base\Traits\HasDefaultRecord;
use GetCandy\Database\Factories\TaxZoneFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaxZone extends BaseModel
{
    use HasFactory;
    use HasDefaultRecord;

    /**
     * Return a new factory instance for the model.
     *
     * @return \GetCandy\Database\Factories\TaxClassFactory
     */
    protected static function newFactory(): TaxZoneFactory
    {
        return TaxZoneFactory::new();
    }

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Define the attribute casting.
     *
     * @var array
     */
    protected $casts = [
        'active'  => 'boolean',
        'default' => 'boolean',
    ];

    /**
     * Return the countries relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function countries()
    {
        return $this->hasMany(TaxZoneCountry::class);
    }

    /**
     * Return the states relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function states()
    {
        return $this->hasMany(TaxZoneState::class);
    }

    /**
     * Return the postcodes relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function postcodes()
    {
        return $this->hasMany(TaxZonePostcode::class);
    }

    /**
     * Return the customer groups relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function customerGroups()
    {
        return $this->hasMany(TaxZoneCustomerGroup::class);
    }

    /**
     * Return the tax rates relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function taxRates()
    {
        return $this->hasMany(TaxRate::class);
    }

    /**
     * Return the tax amounts relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function taxAmounts()
    {
        return $this->hasManyThrough(TaxRateAmount::class, TaxRate::class);
    }
}
