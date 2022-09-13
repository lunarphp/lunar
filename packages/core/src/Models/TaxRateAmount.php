<?php

namespace Lunar\Models;

use Lunar\Base\BaseModel;
use Lunar\Base\Traits\HasMacros;
use Lunar\Database\Factories\TaxRateAmountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaxRateAmount extends BaseModel
{
    use HasFactory;
    use HasMacros;

    /**
     * The tax rate amount.
     *
     * @var Price|null
     */
    public $total;

    /**
     * Return a new factory instance for the model.
     *
     * @return \Lunar\Database\Factories\TaxRateAmountFactory
     */
    protected static function newFactory(): TaxRateAmountFactory
    {
        return TaxRateAmountFactory::new();
    }

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Return the tax rate relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function taxRate()
    {
        return $this->belongsTo(TaxRate::class);
    }

    /**
     * Return the tax class relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function taxClass()
    {
        return $this->belongsTo(TaxClass::class);
    }
}
