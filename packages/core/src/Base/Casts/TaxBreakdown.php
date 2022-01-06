<?php

namespace GetCandy\Base\Casts;

use GetCandy\DataTypes\Price;
use GetCandy\Models\Currency;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Validator;
use stdClass;

class TaxBreakdown implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return \Illuminate\Support\Collection
     */
    public function get($model, $key, $value, $attributes)
    {
        $currency = $model->currency ?: Currency::getDefault();

        return collect(
            json_decode($value, false)
        )->map(function ($rate) use ($currency) {
            $rate->total = new Price($rate->total, $currency, 1);

            return $rate;
        });
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  \GetCandy\DataTypes\Price  $value
     * @param  array  $attributes
     * @return array
     */
    public function set($model, $key, $value, $attributes)
    {
        return [
            $key => json_encode($value),
        ];
    }
}
