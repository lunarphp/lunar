-<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class UpdateTierToQuantityBreakOnPricesTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'prices', function (Blueprint $table) {
            $table->renameColumn('tier', 'quantity_break');
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'prices', function (Blueprint $table) {
            $table->renameColumn('quantity_break', 'tier');
        });
    }
}
