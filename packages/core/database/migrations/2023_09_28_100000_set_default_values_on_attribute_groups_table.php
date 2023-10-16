<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class SetDefaultValuesOnAttributeGroupsTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix . 'attribute_groups', function (Blueprint $table) {
            $table->boolean('position')->default(0)->change();
        });
    }

    public function down()
    {
        Schema::table($this->prefix . 'attribute_groups', function (Blueprint $table) {
            // for not nullable fields `default(null)` removes the default value
            $table->boolean('position')->default(null)->change();
        });
    }
}
