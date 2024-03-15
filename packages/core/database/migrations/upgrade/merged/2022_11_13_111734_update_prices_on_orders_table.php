<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class UpdatePricesOnOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->prefix.'orders', function (Blueprint $table) {
            $table->unsignedBigInteger('sub_total')->change();
            $table->unsignedBigInteger('discount_total')->change();
            $table->unsignedBigInteger('shipping_total')->change();
            $table->unsignedBigInteger('tax_total')->change();
            $table->unsignedBigInteger('total')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->prefix.'orders', function (Blueprint $table) {
            $table->unsignedInteger('sub_total')->change();
            $table->unsignedInteger('discount_total')->change();
            $table->unsignedInteger('shipping_total')->change();
            $table->unsignedInteger('tax_total')->change();
            $table->unsignedInteger('total')->change();
        });
    }
}
