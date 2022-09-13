<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class AddCustomerIdToOrdersTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'orders', function (Blueprint $table) {
            $table->foreignId('customer_id')->after('id')
                ->nullable()
                ->constrained($this->prefix.'customers');
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'orders', function ($table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
        });
    }
}
