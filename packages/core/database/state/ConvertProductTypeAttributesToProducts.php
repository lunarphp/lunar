<?php

namespace Lunar\Database\State;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Models\Product;
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
            ->whereAttributeType(ProductType::class)
            ->update([
                'attribute_type' => Product::class,
            ]);

        DB::table("{$prefix}attribute_groups")
            ->whereAttributableType(ProductType::class)
            ->update([
                'attributable_type' => Product::class,
            ]);
    }

    protected function canRun()
    {
        $prefix = config('lunar.database.table_prefix');

        return Schema::hasTable("{$prefix}attributes") &&
            Schema::hasTable("{$prefix}attribute_groups");
    }
}
