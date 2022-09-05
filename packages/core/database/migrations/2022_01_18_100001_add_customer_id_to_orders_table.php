<?php

use GetCandy\Base\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['customer_id']);
            }
            $table->dropColumn('customer_id');
        });
    }
}
