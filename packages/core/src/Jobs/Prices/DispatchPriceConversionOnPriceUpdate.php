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
use Lunar\Models\Price;

class DispatchPriceConversionOnPriceUpdate implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param Price $savedPrice
     */
    public function __construct(public Price $savedPrice)
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

        $baseCurrency = $this->savedPrice->currency;
        $quoteCurrencies = Currency::default(false)
            ->get()
            ->mapWithKeys(fn (Currency $c) => [$c->id => $c]);

        /*
         * We need base price (e.g. price in default currency),
         * for each of quote prices (e.g. prices in non-default currencies).
         *
         */
        $query = DB::table($pricesTable)
            ->where([
                'priceable_id' => $this->savedPrice->priceable_id,
                'priceable_type' => $this->savedPrice->priceable_type,
            ])
            ->whereNot([
                'currency_id' => $baseCurrency->id,
            ])
            ->select([
                'id          as quote_price_id',
                'currency_id as quote_currency_id',
                'priceable_id',
                'priceable_type',
            ]);

        // init empty batch
        $batchName = sprintf(
            'Price Conversion - Price Update - %s (%s)',
            $this->savedPrice->priceable->product?->translateAttribute('name'),
            $this->savedPrice->priceable->getOption()
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
        // use "quote_price_id" as chunk key
        $query->chunkById(
            $autoConversion['update_chunk_size'],
            function ($records) use ($batch, $quoteCurrencies) {
                $batch->add(new ConvertPrices(
                    collect($records)->map(function ($record) use ($quoteCurrencies) {
                        return [
                            'base_price' => $this->savedPrice->price->decimal,
                            'quote_currency' => $quoteCurrencies[$record->quote_currency_id],
                            'quote_price_id' => $record->quote_price_id,
                            'priceable_id' => $record->priceable_id,
                            'priceable_type' => $record->priceable_type,
                        ];
                    }),
                ));
            }, 'id', 'quote_price_id');

        // associate the job with subject model
        JobBatch::find($batch->id)
            ->subject()
            ->associate($this->savedPrice)
            ->save();
    }
}
