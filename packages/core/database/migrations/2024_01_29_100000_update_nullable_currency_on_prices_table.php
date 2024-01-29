-<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class UpdateNullableCurrencyOnPricesTable extends Migration
{
    public $withinTransaction = true;

    public function up()
    {
        Schema::table($this->prefix.'prices', function (Blueprint $table) {
            $table->foreignId('currency_id')->nullable(false)->change();
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'prices', function (Blueprint $table) {
            $table->foreignId('currency_id')->nullable(true)->change();
        });
    }
}
