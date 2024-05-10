<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class AddNewCustomerFlagToOrdersTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'orders', function (Blueprint $table) {
            $table->boolean('new_customer')->after('channel_id')->default(false)->index();
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'orders', function (Blueprint $table) {
            $table->dropIndex(['new_customer']);
            $table->dropColumn('new_customer');
        });
    }
}
