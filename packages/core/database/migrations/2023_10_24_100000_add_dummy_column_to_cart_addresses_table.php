<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class AddDummyColumnToCartAddressesTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'cart_addresses', function (Blueprint $table) {
            $table->boolean('dummy')->after('shipping_option')->default(false)->index();
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'cart_addresses', function ($table) {
            $table->dropColumn('dummy');
        });
    }
}
