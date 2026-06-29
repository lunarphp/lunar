<?php

namespace Lunar\Core\Actions\Currencies;

use Illuminate\Support\Facades\DB;
use Lunar\Core\Contracts\Actions\Currencies\CreatesCurrencyPrices;
use Lunar\Core\Models\Currency;

class CreateCurrencyPrices implements CreatesCurrencyPrices
{
    public function execute(Currency $incomingCurrency, Currency $baseCurrency): void
    {
        $tablePrefix = config('lunar.database.table_prefix');

        $basePrices = DB::table($tablePrefix.'prices')
            ->select(
                DB::raw('ROUND(price * '.$incomingCurrency->exchange_rate.') as price'),
                DB::raw('ROUND(list_price * '.$incomingCurrency->exchange_rate.') as list_price'),
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
            'list_price',
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
