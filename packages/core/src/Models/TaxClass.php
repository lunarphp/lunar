<?php

namespace GetCandy\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Base\Traits\HasDefaultRecord;
use GetCandy\Base\Traits\HasMacros;
use GetCandy\Database\Factories\TaxClassFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaxClass extends BaseModel
{
    use HasFactory;
    use HasDefaultRecord;
    use HasMacros;

    public static function booted()
    {
        static::updated(function ($taxClass) {
            if ($taxClass->default) {
                TaxClass::whereDefault(true)->where('id', '!=', $taxClass->id)->update([
                    'default' => false,
                ]);
            }
        });

        static::created(function ($taxClass) {
            if ($taxClass->default) {
                TaxClass::whereDefault(true)->where('id', '!=', $taxClass->id)->update([
                    'default' => false,
                ]);
            }
        });
    }

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

    /**
     * Return the ProductVariants relationship.
     *
     * @return HasMany
     */
    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}
