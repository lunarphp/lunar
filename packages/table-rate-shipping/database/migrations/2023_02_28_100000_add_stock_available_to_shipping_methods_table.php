<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class AddStockAvailableToShippingMethodsTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'shipping_methods', function (Blueprint $table) {
            $table->boolean('stock_available')->default(false)->after('cutoff');
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'shipping_methods', function (Blueprint $table) {
            $table->dropColumn('stock_available');
        });
    }
}
