<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class AddQuantityIncrementMinQuantityToProductVariantsTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'product_variants', function (Blueprint $table) {
            $table->integer('quantity_increment')->after('unit_quantity')->unsigned()->default(1)->index();
            $table->integer('min_quantity')->after('unit_quantity')->unsigned()->default(1)->index();
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'product_variants', function ($table) {
            $table->dropColumn('quantity_increment');
        });
        Schema::table($this->prefix.'product_variants', function ($table) {
            $table->dropColumn('min_quantity');
        });
    }
}
