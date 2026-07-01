<?php

namespace Lunar\Core\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\OrderLine;

class DiscountBreakdown implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return Collection
     */
    public function get($model, $key, $value, $attributes)
    {
        $currency = $model->currency ?: Currency::getDefault();

        return collect(
            json_decode($value, false)
        )->map(function ($breakdown) use ($currency) {
            $breakdown->total = new PriceValue((int) $breakdown->total, $currency);
            $breakdown->lines = collect($breakdown->lines)->map(function ($line) {
                return (object) [
                    'quantity' => $line->qty,
                    'line' => OrderLine::find($line->id),
                ];
            });

            return $breakdown;
        });
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  Collection  $value
     * @param  array  $attributes
     * @return array
     */
    public function set($model, $key, $value, $attributes)
    {
        return [
            $key => collect($value)->map(function ($discountLine) {
                return [
                    'discount_id' => $discountLine->discount_id,
                    'promotion_id' => $discountLine->promotion_id ?? null,
                    'promotion_handle' => $discountLine->promotion_handle ?? null,
                    'lines' => $discountLine->lines->map(function ($orderLine) {
                        return [
                            'id' => $orderLine->line->id,
                            'qty' => $orderLine->quantity,
                        ];
                    })->values(),
                    'total' => $discountLine->total->value,
                ];
            })->toJson(),
        ];
    }
}
