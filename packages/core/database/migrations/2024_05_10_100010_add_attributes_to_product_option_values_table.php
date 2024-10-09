<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class AddAttributesToProductOptionValuesTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'product_option_values', function (Blueprint $table) {
            $table->json('attribute_data')->nullable();
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'product_option_values', function ($table) {
            $table->dropColumn('attribute_data');
        });
    }
}
