<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class AddNewCustomerFlagToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->prefix.'orders', function (Blueprint $table) {
            $table->boolean('new_customer')->after('channel_id')->default(false)->index();
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
            $table->dropIndex($this->prefix.'orders_new_customer_index');
            $table->dropColumn('new_customer');
        });
    }
}
