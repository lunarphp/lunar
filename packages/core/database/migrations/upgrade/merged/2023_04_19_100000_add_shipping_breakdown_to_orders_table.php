<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class AddShippingBreakdownToOrdersTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'orders', function (Blueprint $table) {
            $table->json('shipping_breakdown')->nullable()->after('discount_total');
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'orders', function ($table) {
            $table->dropColumn('shipping_breakdown');
        });
    }
}
