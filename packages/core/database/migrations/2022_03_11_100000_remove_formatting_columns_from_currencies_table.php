<?php

use GetCandy\Base\Migration;
use GetCandy\Models\Currency;
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
            $table->string('format')->nullable();
            $table->string('decimal_point')->nullable();
            $table->string('thousand_point')->nullable();
        });
    }
}
