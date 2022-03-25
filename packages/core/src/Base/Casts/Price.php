<?php

namespace GetCandy\Base\Casts;

use GetCandy\DataTypes\Price as PriceDataType;
use GetCandy\Models\Currency;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Validator;

class Price implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return \GetCandy\DataTypes\Price
     */
    public function get($model, $key, $value, $attributes)
    {
        $currency = $model->currency ?: Currency::getDefault();

        /**
         * Make it an integer based on currency requirements.
         */
        $value = preg_replace('/[^0-9]/', '', $value);

        Validator::make([
            $key => $value,
        ], [
            $key => 'nullable|numeric',
        ])->validate();

        return new PriceDataType(
            (int) $value,
            $currency,
            $model->priceable->unit_quantity ?? 1
        );
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
            $key => $value,
        ];
    }
}
