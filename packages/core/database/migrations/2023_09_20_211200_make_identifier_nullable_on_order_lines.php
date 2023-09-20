<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class MakeIdentifierNullableOnOrderLines extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'order_lines', function (Blueprint $table) {
            $table->string('identifier')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'order_lines', function (Blueprint $table) {
            $table->string('identifier')->nullable(false)->change();
        });
    }
}
