<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class AlterConstraintsOnAttributeGroupsTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix . 'attribute_groups', function (Blueprint $table) {
            $table->unique(['attributable_type', 'handle']);
            $table->dropUnique(['handle']);
        });
    }

    public function down()
    {
        Schema::table($this->prefix . 'attribute_groups', function (Blueprint $table) {
            $table->unique(['handle']);
            $table->dropUnique(['attributable_type', 'handle']);
        });
    }
}
