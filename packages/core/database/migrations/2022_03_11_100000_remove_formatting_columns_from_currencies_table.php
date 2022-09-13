<?php

use Lunar\Base\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveFormattingColumnsFromCurrenciesTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'currencies', function (Blueprint $table) {
            $table->dropColumn(['format', 'decimal_point', 'thousand_point']);
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'currencies', function ($table) {
            $table->string('format');
            $table->string('decimal_point');
            $table->string('thousand_point');
        });
    }
}
