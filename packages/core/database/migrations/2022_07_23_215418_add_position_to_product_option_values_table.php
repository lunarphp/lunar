<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class AddPositionToProductOptionValuesTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'product_option_values', function (Blueprint $table) {
            $table->integer('position')->after('name')->default(0)->index();
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'product_option_values', function (Blueprint $table) {
            $table->dropIndex(['position']);
            $table->dropColumn('position');
        });
    }
}
