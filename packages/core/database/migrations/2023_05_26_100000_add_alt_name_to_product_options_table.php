<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class AddAltNameToProductOptionsTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix . 'product_options', function (Blueprint $table) {
            $table->json('alt_name')->nullable()->after('name');
        });
    }

    public function down()
    {
        Schema::table($this->prefix . 'product_options', function ($table) {
            $table->dropColumn('alt_name');
        });
    }
}
