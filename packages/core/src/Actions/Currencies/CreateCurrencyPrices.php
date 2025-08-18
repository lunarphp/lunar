<?php

namespace Lunar\Actions\Currencies;

use Illuminate\Support\Facades\DB;
use Lunar\Models\Contracts\Currency;

class CreateCurrencyPrices
{
    public function handle(Currency $incomingCurrency, Currency $baseCurrency)
    {
        $tablePrefix = config('lunar.database.table_prefix');

        $basePrices = DB::table($tablePrefix.'prices')
            ->select(
                DB::raw('ROUND(price * '.$incomingCurrency->exchange_rate.') as price'),
                DB::raw('ROUND(compare_price * '.$incomingCurrency->exchange_rate.') as compare_price'),
                'priceable_type',
                'customer_group_id',
                'min_quantity',
                'priceable_id',
                DB::raw("'".$incomingCurrency->id."' as currency_id"),
                DB::raw("'".now()."' as created_at"),
                DB::raw("'".now()."' as updated_at")
            )
            ->where('currency_id', $baseCurrency->id);

        DB::table($tablePrefix.'prices')->insertUsing([
            'price',
            'compare_price',
            'priceable_type',
            'customer_group_id',
            'min_quantity',
            'priceable_id',
            'currency_id',
            'created_at',
            'updated_at',
        ], $basePrices);
    }
}
