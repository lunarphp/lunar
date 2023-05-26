<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class AddLabelToProductOptionsTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'product_options', function (Blueprint $table) {
            $table->json('label')->after('name');
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'product_options', function ($table) {
            $table->dropColumn('label');
        });
    }
}
