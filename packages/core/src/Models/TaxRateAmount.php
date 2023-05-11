<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Lunar\Base\BaseModel;
use Lunar\Base\Traits\HasMacros;
use Lunar\Database\Factories\TaxRateAmountFactory;

/**
 * @property int $id
 * @property ?int $tax_class_id
 * @property ?int $tax_rate_id
 * @property float $percentage
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 */
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
