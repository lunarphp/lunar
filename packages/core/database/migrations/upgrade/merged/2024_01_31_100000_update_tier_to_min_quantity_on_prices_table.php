-<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class UpdateTierToMinQuantityOnPricesTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'prices', function (Blueprint $table) {
            $table->renameColumn('tier', 'min_quantity');
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'prices', function (Blueprint $table) {
            $table->renameColumn('min_quantity', 'tier');
        });
    }
}
