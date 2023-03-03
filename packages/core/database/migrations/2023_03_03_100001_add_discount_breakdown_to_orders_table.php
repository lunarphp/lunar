<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class AddDiscountBreakdownToOrdersTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'orders', function (Blueprint $table) {
            $table->json('discount_breakdown')->nullable();
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'orders', function ($table) {
            $table->dropColumn('discount_breakdown');
        });
    }
}
