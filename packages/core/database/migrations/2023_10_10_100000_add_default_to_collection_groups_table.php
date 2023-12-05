<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class AddDefaultToCollectionGroupsTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'collection_groups', function (Blueprint $table) {
            $table->boolean('default')->default(false);
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'collection_groups', function ($table) {
            $table->dropColumn('default');
        });
    }
}
