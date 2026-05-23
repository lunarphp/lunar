<?php

namespace Lunar\Core\Base\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Lunar\Core\DataTypes\Price as PriceDataType;
use Lunar\Core\Models\Currency;

class Price implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return PriceDataType
     */
    public function get($model, $key, $value, $attributes)
    {
        $currency = $model->currency ?: Currency::getDefault();

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
            $this->resolveUnitQuantity($model),
        );
    }

    protected function resolveUnitQuantity(Model $model): int
    {
        if ($model->isRelation('priceable')) {
            $priceable = $model->priceable;

            if ($priceable !== null && isset($priceable->unit_quantity)) {
                return (int) $priceable->unit_quantity;
            }
        }

        $attributes = $model->getAttributes();

        return (int) ($attributes['unit_quantity'] ?? 1);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  PriceDataType  $value
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
