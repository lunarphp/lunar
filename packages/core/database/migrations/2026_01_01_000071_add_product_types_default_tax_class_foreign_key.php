<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * Adds the `product_types.default_tax_class_id` foreign key.
 *
 * `product_types` is created before `tax_classes`, so the column is declared
 * inline in `create_product_types_table` and the constraint is added here,
 * once both tables exist.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'product_types', function (Blueprint $table) {
            $table->foreign('default_tax_class_id')
                ->references('id')
                ->on($this->prefix.'tax_classes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'product_types', function (Blueprint $table) {
            $table->dropForeign(['default_tax_class_id']);
        });
    }
};
