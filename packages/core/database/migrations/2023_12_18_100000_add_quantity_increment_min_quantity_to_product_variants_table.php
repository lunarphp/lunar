<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'product_variants', function (Blueprint $table) {
            $table->integer('quantity_increment')->after('unit_quantity')->unsigned()->default(1)->index();
            $table->integer('min_quantity')->after('unit_quantity')->unsigned()->default(1)->index();
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'product_variants', function (Blueprint $table) {
            $table->dropIndex(['quantity_increment']);
            $table->dropColumn('quantity_increment');
        });

        Schema::table($this->prefix.'product_variants', function (Blueprint $table) {
            $table->dropIndex(['min_quantity']);
            $table->dropColumn('min_quantity');
        });
    }
};
