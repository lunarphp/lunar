<?php

namespace Lunar\Jobs\Prices;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Lunar\Models\Currency;

class ConvertPrices implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param Collection<array> $records
     */
    public function __construct(public Collection $records)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = [];

        // prepare price data to update
        // NOTE: we include priceable_id/priceable_type in data, but don't update them.
        // if we don't include them, rdbms complains about not-null fields
        foreach ($this->records->unique('quote_price_id') as $record) {
            /** @var Currency $quoteCurrency */
            $quoteCurrency = $record['quote_currency'];

            $value = (int)bcmul($record['base_price'], $quoteCurrency->exchange_rate);
            $newQuotePrice = new \Lunar\DataTypes\Price($value, $quoteCurrency);

            $data[] = [
                'id' => $record['quote_price_id'],
                'price' => $newQuotePrice->value,
                'priceable_id' => $record['priceable_id'],
                'priceable_type' => $record['priceable_type'],
            ];
        }

        // mass update prices records
        $prefix = config('lunar.database.table_prefix');
        try {
            DB::table("{$prefix}prices")->upsert($data, ['id'], ['price']);
        } catch (\Throwable $e) {
            $this->fail($e);
        }
    }
}
