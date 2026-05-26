<?php

namespace Lunar\Core\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Lunar\Core\Base\ValueObjects\Cart\ShippingBreakdownItem;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Currency;

class ShippingBreakdown implements CastsAttributes, SerializesCastableAttributes
{
    /**
     * Cast the given value.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return \Lunar\Core\Base\ValueObjects\Cart\ShippingBreakdown
     */
    public function get($model, $key, $value, $attributes)
    {
        $breakdown = new \Lunar\Core\Base\ValueObjects\Cart\ShippingBreakdown;

        $breakdown->items = collect(
            json_decode($value, false)
        )->mapWithKeys(function ($shipping, $key) {
            $currency = Currency::whereCode($shipping->currency->code)->first();

            return [
                $key => new ShippingBreakdownItem(
                    name: $shipping->name,
                    identifier: $shipping->identifier,
                    price: new PriceValue((int) $shipping->value, $currency),
                ),
            ];
        });

        return $breakdown;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  \Lunar\Core\Base\ValueObjects\Cart\ShippingBreakdown  $value
     * @param  array  $attributes
     * @return array
     */
    public function set($model, $key, $value, $attributes)
    {
        if ($value && ! is_a($value, \Lunar\Core\Base\ValueObjects\Cart\ShippingBreakdown::class)) {
            throw new \Exception('Shipping breakdown must be instance of Lunar\Core\Base\ValueObjects\Cart\ShippingBreakdown');
        }

        if (! $value) {
            return [];
        }

        return [
            $key => $value->items->map(function ($item) {
                $currency = $item->price->resolveCurrency();

                return [
                    'name' => $item->name,
                    'identifier' => $item->identifier,
                    'value' => $item->price->value,
                    'formatted' => $item->price->format(),
                    'currency' => $currency->toArray(),
                ];
            })->toJson(),
        ];
    }

    /**
     * Get the serialized representation of the value.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  Collection  $value
     * @param  array<string, mixed>  $attributes
     */
    public function serialize($model, $key, $value, $attributes)
    {
        return json_encode(
            $this->set($model, $key, $value, $attributes)
        );
    }
}
