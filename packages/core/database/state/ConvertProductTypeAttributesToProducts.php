<?php

namespace Lunar\Core\Database\State;

use Illuminate\Support\Facades\Schema;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\ProductType;

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
                ProductType::morphName()
            )
            ->update([
                'attribute_type' => 'product',
            ]);

        DB::table("{$prefix}attribute_groups")
            ->whereAttributableType(
                ProductType::morphName()
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
