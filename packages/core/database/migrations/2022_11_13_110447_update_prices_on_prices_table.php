<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class UpdatePricesOnPricesTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'prices', function (Blueprint $table) {
            $table->unsignedBigInteger('price')->change();
            $table->unsignedBigInteger('compare_price')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'prices', function (Blueprint $table) {
            $table->unsignedInteger('price')->change();
            $table->unsignedInteger('compare_price')->change();
        });
    }
}
