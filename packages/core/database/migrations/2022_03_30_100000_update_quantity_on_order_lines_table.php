<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class UpdateQuantityOnOrderLinesTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'order_lines', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->change();
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'order_lines', function ($table) {
            $table->smallInteger('quantity')->unsigned()->change();
        });
    }
}
