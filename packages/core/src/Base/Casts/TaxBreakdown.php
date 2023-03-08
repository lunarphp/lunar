<?php

namespace Lunar\Base\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Lunar\DataTypes\Price;
use Lunar\Models\Currency;

class TaxBreakdown implements CastsAttributes, SerializesCastableAttributes
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
     * @param  \Lunar\DataTypes\Price  $value
     * @param  array  $attributes
     * @return array
     */
    public function set($model, $key, $value, $attributes)
    {
        return [
            $key => json_encode(collect($value)->map(function ($rate) {
                if (! is_array($rate)) {
                    if ($rate->total instanceof Price) {
                        $rate->total = $rate->total->value;
                    }
                }
                return $rate;
            })->values()),
        ];
    }
    
    /**
     * Get the serialized representation of the value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  \Illuminate\Support\Collection  $value
     * @param  array<string, mixed>  $attributes
     */
    public function serialize($model, $key, $value, $attributes)
    {
        return json_encode($value->map(function ($rate) {
            if ($rate->total instanceof Price) {
                $rate->total = (object) [
                    'value' => $rate->total->value,
                    'formatted' => $rate->total->formatted,    
                    'currency' => $rate->total->currency->toArray(),
                ];
            }
            return $rate;
        })
        ->values());
    }
}
