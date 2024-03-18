-<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class AddMissingPkToProductProductOptionsTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'product_product_option', function (Blueprint $table) {
            $table->id()->first();
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'product_product_option', function (Blueprint $table) {
            $table->removeColumn('id');
        });
    }
}
