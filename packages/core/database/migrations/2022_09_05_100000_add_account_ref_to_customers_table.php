<?php

use Lunar\Base\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountRefToCustomersTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'customers', function (Blueprint $table) {
            $table->string('account_ref')->nullable()->index()->after('vat_no');
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'customers', function (Blueprint $table) {
            $table->dropColumn('account_ref');
        });
    }
}
