<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;
use Lunar\Facades\DB;

class AddAttributesToBrandsTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'brands', function (Blueprint $table) {
            $table->json('attribute_data')->after('name')->nullable();
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'brands', function ($table) {
            $table->dropColumn('attribute_data');
        });
    }
}
