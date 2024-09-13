<?php

namespace Lunar\Database\State;

use Illuminate\Support\Facades\Schema;
use Lunar\Facades\DB;
use Lunar\Models\ProductType;

class ConvertProductTypeAttributesToProducts
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

        DB::table("{$prefix}attributes")
            ->whereAttributeType(
                (new ProductType)->getMorphClass()
            )
            ->update([
                'attribute_type' => 'product',
            ]);

        DB::table("{$prefix}attribute_groups")
            ->whereAttributableType(
                (new ProductType)->getMorphClass()
            )
            ->update([
                'attributable_type' => 'product',
            ]);
    }

    protected function canRun()
    {
        $prefix = config('lunar.database.table_prefix');

        return Schema::hasTable("{$prefix}attributes") &&
            Schema::hasTable("{$prefix}attribute_groups");
    }
}
