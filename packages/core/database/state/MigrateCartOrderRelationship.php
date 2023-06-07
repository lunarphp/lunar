<?php

namespace Lunar\Database\State;

use Illuminate\Support\Facades\Schema;
use Lunar\Facades\DB;

class MigrateCartOrderRelationship
{
    public function prepare()
    {
        //
    }

    public function run()
    {
        $prefix = config('lunar.database.table_prefix');

        if (! $this->canRun()) {
            return;
        }

        DB::transaction(function () use ($prefix) {
            DB::table("{$prefix}carts")->whereNotNull('order_id')->select([
                'id',
                'order_id',
            ])->get()->each(function ($cart) use ($prefix) {
                DB::table("{$prefix}orders")->where('id', $cart->order_id)->update([
                    'cart_id' => $cart->id,
                ]);
            });
        });
    }

    protected function canRun()
    {
        $prefix = config('lunar.database.table_prefix');

        return Schema::hasColumn("{$prefix}carts", 'order_id');
    }
}
