<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class RemovePositionFromProductOptionsTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'product_options', function (Blueprint $table) {
            $table->dropIndex($this->prefix.'product_options_position_index');
            $table->dropColumn('position');
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'product_options', function (Blueprint $table) {
            $table->smallInteger('position')->after('label');
        });
    }
}
