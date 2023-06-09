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
        DB::select("
            update {$prefix}orders set cart_id = (select id from {$prefix}carts where order_id = {$prefix}orders.id)
            where exists (select 1 from {$prefix}carts where {$prefix}orders.id = {$prefix}carts.order_id and {$prefix}carts.order_id is not null)
        ");
        DB::select("update {$prefix}carts set order_id = null");
    }

    protected function canRun()
    {
        $prefix = config('lunar.database.table_prefix');

        return Schema::hasColumn("{$prefix}carts", 'order_id');
    }
}
