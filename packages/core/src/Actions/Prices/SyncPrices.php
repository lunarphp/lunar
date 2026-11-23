<?php

namespace Lunar\Core\Actions\Prices;

use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Contracts\Actions\Prices\SyncsPrices;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Price;

/**
 * Replace a priceable's price rows with the given set. Rows are matched by
 * currency, customer group and minimum quantity: a matching row keeps its id
 * and takes the new amounts, a new combination gets a row, and rows for
 * combinations no longer listed are deleted. When the set names a
 * combination twice, the last entry wins.
 */
class SyncPrices implements SyncsPrices
{
    public function execute(Model $priceable, array $prices): Model
    {
        return DB::transaction(function () use ($priceable, $prices): Model {
            $wanted = [];

            foreach ($prices as $row) {
                $attributes = [
                    'currency_id' => (int) $row['currency_id'],
                    'customer_group_id' => isset($row['customer_group_id']) ? (int) $row['customer_group_id'] : null,
                    'min_quantity' => max(1, (int) $row['min_quantity']),
                    'price' => (int) $row['price'],
                    'list_price' => isset($row['list_price']) ? (int) $row['list_price'] : null,
                ];

                $wanted[$this->tuple($attributes)] = $attributes;
            }

            $existing = $priceable->prices()->with('currency')->get();
            $currencies = Currency::query()->findMany(array_column($wanted, 'currency_id'))->keyBy('id');

            // Delete per model so price observers and cache invalidation run.
            foreach ($existing as $price) {
                /** @var Price $price */
                $tuple = $this->tuple($price->only(['currency_id', 'customer_group_id', 'min_quantity']));

                if (! isset($wanted[$tuple])) {
                    $price->delete();

                    continue;
                }

                $attributes = $wanted[$tuple];
                unset($wanted[$tuple]);

                if ((int) $price->price !== $attributes['price'] || $price->list_price !== $attributes['list_price']) {
                    $price->update(['price' => $attributes['price'], 'list_price' => $attributes['list_price']]);
                }
            }

            foreach ($wanted as $attributes) {
                // Set the currency up front: the price observer reads it and
                // lazy loading is disabled.
                $price = $priceable->prices()->make($attributes);
                $price->setRelation('currency', $currencies->get($attributes['currency_id']));
                $price->save();
            }

            $priceable->unsetRelation('prices');

            return $priceable;
        });
    }

    /** @param  array<string, mixed>  $row */
    protected function tuple(array $row): string
    {
        return ((int) $row['currency_id']).':'.((int) ($row['customer_group_id'] ?? 0)).':'.((int) $row['min_quantity']);
    }
}
