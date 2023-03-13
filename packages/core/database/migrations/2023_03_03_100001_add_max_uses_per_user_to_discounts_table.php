<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class AddMaxUsesPerUserToDiscountsTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'discounts', function (Blueprint $table) {
            $table->json('max_uses_per_user')->nullable()->after('max_uses');
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'discounts', function ($table) {
            $table->dropColumn('max_uses_per_user');
        });
    }
}
