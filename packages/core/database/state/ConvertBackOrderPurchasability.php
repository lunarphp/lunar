<?php

namespace Lunar\Database\State;

use Lunar\Facades\DB;

class ConvertBackOrderPurchasability
{
    public function prepare()
    {
        //
    }

    public function run()
    {
        DB::usingConnection(config('lunar.database.connection') ?: DB::getDefaultConnection(), function () {
            $prefix = config('lunar.database.table_prefix');
            if ($this->shouldRun()) {
                DB::table("{$prefix}product_variants")->where([
                    'purchasable' => 'backorder',
                ])->update([
                    'purchasable' => 'in_stock_on_backorder',
                ]);
            }
        });
    }

    protected function shouldRun(): bool
    {
        $prefix = config('lunar.database.table_prefix');

        return (bool) DB::table("{$prefix}product_variants")->where([
            'purchasable' => 'backorder',
        ])->count();
    }
}
