<?php

namespace Lunar\Jobs\Prices;

use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Lunar\Models\Currency;
use Lunar\Models\JobBatch;

class DispatchPriceConversionOnCurrencyUpdate implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param Currency $savedCurrency
     */
    public function __construct(public Currency $savedCurrency)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Throwable
     */
    public function handle()
    {
        $autoConversion = config('lunar.pricing.auto_conversion');

        $prefix = config('lunar.database.table_prefix');
        $pricesTable = "{$prefix}prices";

        $baseCurrency = Currency::getDefault();

        /*
         * We need base price (e.g. price in default currency),
         * for each of quote prices (e.g. prices in non-default currencies).
         *
         */
        $query = DB::table($pricesTable, 'p1')
            ->join("{$pricesTable} AS p2", function (JoinClause $join) use ($baseCurrency) {
                $join->on('p1.priceable_id', '=', 'p2.priceable_id')
                    ->on('p1.priceable_type', '=', 'p2.priceable_type');
            })
            ->where('p1.currency_id', '=', $baseCurrency->id)
            ->where('p2.currency_id', '!=', $baseCurrency->id)
            ->select([
                'p1.id             as base_price_id',
                'p1.currency_id    as base_currency_id',
                'p1.price          as base_price',
                'p2.id             as quote_price_id',
                'p2.currency_id    as quote_currency_id',
                'p2.priceable_id   as priceable_id',
                'p2.priceable_type as priceable_type',
            ]);

        // init empty batch
        $batchName = sprintf(
            'Price Conversion - Currency Update - %s (%s)',
            $this->savedCurrency->name,
            $this->savedCurrency->code
        );
        $batch = Bus::batch([])
            ->name($batchName)
            ->withOption('tag', 'Price Conversion')
            ->onConnection($autoConversion['connection'])
            ->onQueue($autoConversion['queue'])
            ->then(function (Batch $batch) {
                // All jobs completed successfully...
            })->catch(function (Batch $batch, \Throwable $e) {
                // First batch job failure detected...
            })->finally(function (Batch $batch) {
                // The batch has finished executing...
            })->dispatch();

        // fetch query results in chunks, and add them into the batch
        // use "base_price_id" as chunk key
        $query->chunkById(
            $autoConversion['update_chunk_size'],
            function ($records) use ($batch, $baseCurrency) {
                $batch->add(new ConvertPrices(
                    collect($records)->map(function ($record) use ($baseCurrency) {
                        $basePrice = new \Lunar\DataTypes\Price($record->base_price, $baseCurrency);
                        return [
                            'base_price' => $basePrice->decimal,
                            'quote_currency' => $this->savedCurrency,
                            'quote_price_id' => $record->quote_price_id,
                            'priceable_id' => $record->priceable_id,
                            'priceable_type' => $record->priceable_type,
                        ];
                    })
                ));
            }, 'p1.id', 'base_price_id');

        // associate the job with subject model
        JobBatch::find($batch->id)
            ->subject()
            ->associate($this->savedCurrency)
            ->save();
    }
}
