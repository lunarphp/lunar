<?php

namespace Lunar\Base\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Support\Collection;
use Lunar\DataTypes\Price;
use Lunar\Models\Currency;

class ShippingBreakdown implements CastsAttributes, SerializesCastableAttributes
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
        )->map(function ($shipping) use ($currency) {
            $shipping->price = new Price($shipping->price, $currency, 1);

            return $shipping;
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
        if (! $value instanceof Collection) {
            $value = $value->items;
        }

        return $value->map(function ($rate) {
            $data = [
                'name' => $rate->name,
                'identifier' => $rate->identifier,
                'price' => $rate->price,
            ];

            if (! is_array($rate)) {
                if ($rate->price instanceof Price) {
                    $data['price'] = $rate->price->value;
                }
            }

            return $data;
        })->values();
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
        return $value->map(function ($rate) {
            if ($rate->price instanceof Price) {
                $rate->total = (object) [
                    'name' => $rate->name,
                    'identifier' => $rate->identifier,
                    'total' => $rate->total->value,
                ];
            }

            return $rate;
        })->toJson();
    }
}
