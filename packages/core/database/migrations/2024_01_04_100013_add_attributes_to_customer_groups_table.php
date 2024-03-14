<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class AddAttributesToCustomerGroupsTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'customer_groups', function (Blueprint $table) {
            $table->json('attribute_data')->after('default')->nullable();
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'customer_groups', function ($table) {
            $table->dropColumn('attribute_data');
        });
    }
}
