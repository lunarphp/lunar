<?php

use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;
use Lunar\Facades\DB;

return new class extends Migration
{
    public function up(): void
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
        ])->groupBy([
            "{$productsTable}.id",
            "{$optionsTable}.id",
            "{$optionsTable}.position",
        ])
            ->orderBy("{$productsTable}.id")
            ->chunk(200, function ($rows) {
                DB::table(
                    $this->prefix.'product_product_option'
                )->insert(
                    $rows->map(
                        fn ($row) => (array) $row
                    )->toArray()
                );
            });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'product_product_option');
    }
};
