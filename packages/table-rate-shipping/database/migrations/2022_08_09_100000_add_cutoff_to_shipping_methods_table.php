<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class AddCutoffToShippingMethodsTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'shipping_methods', function (Blueprint $table) {
            $table->time('cutoff')->nullable()->after('enabled');
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'shipping_methods', function (Blueprint $table) {
            $table->dropColumn('cutoff');
        });
    }
}
