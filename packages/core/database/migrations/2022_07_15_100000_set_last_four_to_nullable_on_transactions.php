<?php

use GetCandy\Base\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetLastFourToNullableOnTransactions extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'transactions', function (Blueprint $table) {
            $table->smallInteger('last_four')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'transactions', function ($table) {
            $table->smallInteger('last_four')->nullable(false)->change();
        });
    }
}
