<?php

namespace GetCandy\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Database\Factories\TaxClassFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaxClass extends BaseModel
{
    use HasFactory;

    /**
     * Return a new factory instance for the model.
     *
     * @return \GetCandy\Database\Factories\TaxClassFactory
     */
    protected static function newFactory(): TaxClassFactory
    {
        return TaxClassFactory::new();
    }

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Return the tax rate amounts relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function taxRateAmounts()
    {
        return $this->hasMany(TaxRateAmount::class);
    }
}
