<?php

namespace Lunar\Jobs\Currencies;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Lunar\Models\Contracts\Price;
use Lunar\Models\Currency;

class SyncPriceCurrencies implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $tries = 1;

    public function __construct(protected Price $price) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $currencies = Currency::where('id', '!=', $this->price->currency_id)
            ->where('sync_prices', true)
            ->get();

        foreach ($currencies as $currency) {
            $priceCounterpart = \Lunar\Models\Price::where('priceable_id', $this->price->priceable_id)
                ->where('priceable_type', $this->price->priceable_type)
                ->where('currency_id', $currency->id)
                ->where('id', '!=', $this->price->id)
                ->where('min_quantity', $this->price->min_quantity)
                ->where('customer_group_id', $this->price->customer_group_id)
                ->first();

            if (! $priceCounterpart) {
                $priceCounterpart = (new \Lunar\Models\Price)->forceFill([
                    ...Arr::except($this->price->getAttributes(), ['id']),
                    'currency_id' => $currency->id,
                    'price' => $this->price->price->value * $currency->exchange_rate,
                    'compare_price' => $this->price->compare_price->value * $currency->exchange_rate,
                ]);

                $priceCounterpart->saveQuietly();

                continue;
            }

            $priceCounterpart->price = $this->price->price->value * $currency->exchange_rate;
            $priceCounterpart->compare_price = $this->price->compare_price->value * $currency->exchange_rate;
            $priceCounterpart->saveQuietly();
        }
    }
}
