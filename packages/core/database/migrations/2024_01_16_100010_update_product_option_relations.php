<?php

use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;
use Lunar\Facades\DB;

class UpdateProductOptionRelations extends Migration
{
    public $withinTransaction = true;

    public function up()
    {
        $variantsTable = $this->prefix.'product_variants';
        $productsTable = $this->prefix.'products';
        $optionsTable = $this->prefix.'product_options';
        $optionValueTable = $this->prefix.'product_option_values';
        $variantOptionsValueTable = $this->prefix.'product_option_value_product_variant';

        DB::table($variantOptionsValueTable)->join(
            $variantsTable,
            "{$variantOptionsValueTable}.variant_id",
            '=',
            "{$variantsTable}.id"
        )->join(
            $optionValueTable,
            "{$variantOptionsValueTable}.value_id",
            '=',
            "{$optionValueTable}.id"
        )->join(
            $optionsTable,
            "{$optionValueTable}.product_option_id",
            '=',
            "{$optionsTable}.id"
        )->join(
            $productsTable,
            "{$variantsTable}.product_id",
            '=',
            "{$productsTable}.id"
        )->select([
            "{$productsTable}.id as product_id",
            "{$optionsTable}.id as product_option_id",
            "{$optionsTable}.position",
        ])->groupBy(['product_id', 'product_option_id'])
            ->orderBy('product_id')
            ->chunk(2, function ($rows) {
                DB::table(
                    $this->prefix.'product_product_option'
                )->insert(
                    $rows->map(
                        fn ($row) => (array) $row
                    )->toArray()
                );
            });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'product_product_option');
    }
}
