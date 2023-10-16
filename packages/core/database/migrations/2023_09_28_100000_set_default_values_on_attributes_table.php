<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class SetDefaultValuesOnAttributesTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix . 'attributes', function (Blueprint $table) {
            $table->boolean('required')->default(0)->change();
            $table->boolean('system')->default(0)->change();
            $table->boolean('position')->default(0)->change();
        });
    }

    public function down()
    {
        Schema::table($this->prefix . 'attributes', function (Blueprint $table) {
            // for not nullable fields `default(null)` removes the default value
            $table->boolean('required')->default(null)->change();
            $table->boolean('system')->default(null)->change();
            $table->boolean('position')->default(null)->change();
        });
    }
}
