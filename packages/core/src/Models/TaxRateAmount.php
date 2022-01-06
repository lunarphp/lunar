<?php

namespace GetCandy\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Database\Factories\TaxRateAmountFactory;
use GetCandy\Models\TaxClass;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaxRateAmount extends BaseModel
{
    use HasFactory;

    /**
     * The tax rate amount.
     *
     * @var Price|null
     */
    public $total;

    /**
     * Return a new factory instance for the model.
     *
     * @return \GetCandy\Database\Factories\TaxRateFactory
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
