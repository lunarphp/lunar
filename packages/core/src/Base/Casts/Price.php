<?php

namespace Lunar\Base\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Lunar\DataTypes\Price as PriceDataType;
use Lunar\Exceptions\InvalidDataTypeValueException;
use Lunar\Models\Currency;

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
            $model->priceable->unit_quantity ?? $model->unit_quantity ?? 1,
        );
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
        // The column counts minor units and cannot hold a fraction of one, so a
        // decimal is rounded by the database and silently becomes that many
        // minor units - 12.99 is stored as 13. It cannot be converted here
        // either: the cast has no way to tell whether a bare 12 means twelve
        // minor units or twelve of the major unit, and guessing would multiply
        // every correctly written price by the currency factor.
        if (! is_null($value) && is_numeric($value) && (float) $value != (int) $value) {
            throw new InvalidDataTypeValueException(
                trim(sprintf(
                    'Prices are stored as a whole number of minor units, so [%s] cannot be %s. %s',
                    $key,
                    $value,
                    $this->suggestion($model, $value),
                ))
            );
        }

        return [
            $key => $value,
        ];
    }

    /**
     * The value the caller most likely meant, when the currency is known.
     */
    protected function suggestion($model, $value): string
    {
        $factor = $model->currency?->factor ?? Currency::getDefault()?->factor;

        return $factor
            ? sprintf('Did you mean %d?', (int) round($value * $factor))
            : '';
    }
}
