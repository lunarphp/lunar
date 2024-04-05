<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

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
            $table->dropIndex($this->prefix.'tax_classes_default_index');
            $table->dropColumn('default');
        });
    }
}
