<?php

namespace Lunar\Base\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Lunar\Base\ValueObjects\Cart\ShippingBreakdownItem;
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
     * @return \Lunar\Base\ValueObjects\Cart\ShippingBreakdown
     */
    public function get($model, $key, $value, $attributes)
    {
        $breakdown = new \Lunar\Base\ValueObjects\Cart\ShippingBreakdown;

        $breakdown->items = collect(
            json_decode($value, false)
        )->mapWithKeys(function ($shipping, $key) {
            $currency = Currency::whereCode($shipping->currency->code)->first();

            return [
                $key => new ShippingBreakdownItem(
                    name: $shipping->name,
                    identifier: $shipping->identifier,
                    price: new Price($shipping->value, $currency, 1),
                ),
            ];
        });

        return $breakdown;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  \Lunar\Base\ValueObjects\Cart\ShippingBreakdown  $value
     * @param  array  $attributes
     * @return array
     */
    public function set($model, $key, $value, $attributes)
    {
        if ($value && ! is_a($value, \Lunar\Base\ValueObjects\Cart\ShippingBreakdown::class)) {
            throw new \Exception('Shipping breakdown must be instance of Lunar\Base\ValueObjects\Cart\ShippingBreakdown');
        }

        if (! $value) {
            return [];
        }

        return [
            $key => $value->items->map(function ($item) {
                return [
                    'name' => $item->name,
                    'identifier' => $item->identifier,
                    'value' => $item->price->value,
                    'formatted' => $item->price->formatted,
                    'currency' => $item->price->currency->toArray(),
                ];
            })->toJson(),
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
        return json_encode(
            $this->set($model, $key, $value, $attributes)
        );
    }
}
