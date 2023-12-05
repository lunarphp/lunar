<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class AddDefaultToProductTypesTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'product_types', function (Blueprint $table) {
            $table->boolean('default')->default(false);
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'product_types', function ($table) {
            $table->dropColumn('default');
        });
    }
}
