<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class AddAttributesToCustomersTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'customers', function (Blueprint $table) {
            $table->json('attribute_data')->after('vat_no')->nullable();
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'customers', function ($table) {
            $table->dropColumn('attribute_data');
        });
    }
}
