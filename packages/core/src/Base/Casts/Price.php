<?php

namespace Lunar\Base\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Validator;
use Lunar\DataTypes\Price as PriceDataType;
use Lunar\Models\Currency;

class Price implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return \Lunar\DataTypes\Price
     */
    public function get($model, $key, $value, $attributes)
    {
        $currency = $model->currency ?: Currency::modelClass()::getDefault();

        if (! is_null($value)) {
            /**
             * Make it an integer based on currency requirements.
             */
            $value = preg_replace('/[^0-9]/', '', $value);
        }

        Validator::make([
            $key => $value,
        ], [
            $key => 'nullable|numeric',
        ])->validate();

        return new PriceDataType(
            (int) $value,
            $currency,
            $model->priceable->unit_quantity ?? $model->unit_quantity ?? 1,
        );
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  \Lunar\DataTypes\Price  $value
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
