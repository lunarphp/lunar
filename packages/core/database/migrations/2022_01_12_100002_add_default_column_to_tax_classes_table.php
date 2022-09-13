<?php

use Lunar\Base\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultColumnToTaxClassesTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'tax_classes', function (Blueprint $table) {
            $table->boolean('default')->after('name')->index()->default(false);
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'tax_classes', function (Blueprint $table) {
            $table->dropColumn('default');
        });
    }
}
