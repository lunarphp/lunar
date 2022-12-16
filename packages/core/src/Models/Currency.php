<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Lunar\Base\BaseModel;
use Lunar\Base\Traits\HasDefaultRecord;
use Lunar\Base\Traits\HasJobBatches;
use Lunar\Base\Traits\HasMacros;
use Lunar\Base\Traits\LogsActivity;
use Lunar\Database\Factories\CurrencyFactory;

class Currency extends BaseModel
{
    use HasFactory;
    use LogsActivity;
    use HasDefaultRecord;
    use HasMacros;
    use HasJobBatches;

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
     * @return \Lunar\Database\Factories\CurrencyFactory
     */
    protected static function newFactory(): CurrencyFactory
    {
        return CurrencyFactory::new();
    }

    /**
     * Return the prices relationship
     *
     * @return HasMany
     */
    public function prices()
    {
        return $this->hasMany(Price::class);
    }

    /**
     * Returns the amount we need to multiply or divide the price
     * for the cents/pence.
     *
     * @return string
     */
    public function getFactorAttribute()
    {
        /**
         * If we figure out how many decimal places we need, we can work
         * out what the initial divided value should be to get the cents.
         *
         * E.g. For two decimal places, we need to divide by 100.
         */
        if ($this->decimal_places < 1) {
            return 1;
        }

        return sprintf("1%0{$this->decimal_places}d", 0);
    }
}
