<?php

namespace Lunar\Core\Actions\Currencies;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Lunar\Core\Contracts\Actions\Currencies\CreatesCurrencyPrices;
use Lunar\Core\Models\Currency;

class CreateCurrencyPrices implements CreatesCurrencyPrices
{
    /**
     * How many base prices are read and inserted per batch.
     */
    protected int $chunkSize = 500;

    /**
     * Copy every base currency price into the incoming currency, converted by
     * its exchange rate. Rows are written with bulk inserts rather than through
     * the model, so each one is given its own `public_id` here, in the same
     * ULID format `HasPublicId` mints on create. The batches share one
     * transaction, so a failure part way through copies nothing.
     */
    public function execute(Currency $incomingCurrency, Currency $baseCurrency): void
    {
        $table = config('lunar.database.table_prefix').'prices';
        $timestamp = now();

        DB::transaction(function () use ($table, $incomingCurrency, $baseCurrency, $timestamp) {
            DB::table($table)
                ->select(
                    'id',
                    DB::raw('ROUND(price * '.$incomingCurrency->exchange_rate.') as price'),
                    DB::raw('ROUND(list_price * '.$incomingCurrency->exchange_rate.') as list_price'),
                    'priceable_type',
                    'customer_group_id',
                    'min_quantity',
                    'priceable_id',
                )
                ->where('currency_id', $baseCurrency->id)
                ->chunkById($this->chunkSize, function (Collection $basePrices) use ($table, $incomingCurrency, $timestamp) {
                    DB::table($table)->insert($basePrices->map(fn (object $basePrice): array => [
                        'public_id' => (string) Str::ulid(),
                        'price' => (int) $basePrice->price,
                        'list_price' => $basePrice->list_price === null ? null : (int) $basePrice->list_price,
                        'priceable_type' => $basePrice->priceable_type,
                        'customer_group_id' => $basePrice->customer_group_id,
                        'min_quantity' => $basePrice->min_quantity,
                        'priceable_id' => $basePrice->priceable_id,
                        'currency_id' => $incomingCurrency->id,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ])->all());
                });
        });
    }
}
