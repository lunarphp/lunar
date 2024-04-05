<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class AddColumnsToAttributesTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'attributes', function (Blueprint $table) {
            $table->boolean('searchable')->after('system')->default(true)->index();
            $table->boolean('filterable')->after('system')->default(false)->index();
            $table->string('validation_rules')->after('system')->nullable();
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'attributes', function (Blueprint $table) {
            $table->dropIndex(['searchable']);
            $table->dropIndex(['filterable']);
            $table->dropColumn(['searchable', 'filterable', 'validation_rules']);
        });
    }
}
