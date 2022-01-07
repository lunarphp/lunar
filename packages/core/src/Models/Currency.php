<?php

namespace GetCandy\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Base\Traits\HasDefaultRecord;
use GetCandy\Base\Traits\LogsActivity;
use GetCandy\Database\Factories\CurrencyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Currency extends BaseModel
{
    use HasFactory;
    use LogsActivity;
    use HasDefaultRecord;
    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Return a new factory instance for the model.
     *
     * @return \GetCandy\Database\Factories\CurrencyFactory
     */
    protected static function newFactory(): CurrencyFactory
    {
        return CurrencyFactory::new();
    }

    /**
     * Returns the amount we need to multiply or divide the price
     * for the cents/pence.
     *
     * @return void
     */
    public function getFactorAttribute()
    {
        /**
         * If we figure out how many decimal places we need, we can work
         * out what the initial divided value should be to get the cents.
         *
         * E.g. For two decimal places, we need to divide by 100.
         */
        return sprintf("1%0{$this->decimal_places}d", 0);
    }
}
