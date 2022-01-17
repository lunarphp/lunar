<?php

namespace GetCandy\Database\State;

use GetCandy\Models\Product;
use GetCandy\Models\ProductType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConvertProductTypeAttributesToProducts
{
    public function run()
    {
        $prefix = config('getcandy.database.table_prefix');

        if (!$this->canRun()) {
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
        $prefix = config('getcandy.database.table_prefix');

        return Schema::hasTable("{$prefix}attributes") &&
            Schema::hasTable("{$prefix}attribute_groups");
    }
}
