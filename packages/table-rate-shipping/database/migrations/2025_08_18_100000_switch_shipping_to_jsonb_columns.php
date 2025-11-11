<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up()
    {
        // Only run for PostgreSQL - MySQL has no change and SQLite will error when trying to change
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        Schema::table($this->prefix.'shipping_methods', function (Blueprint $table) {
            $table->jsonb('data')->nullable()->change();
        });
    }

    public function down()
    {
        // Only run for PostgreSQL
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        Schema::table($this->prefix.'shipping_methods', function (Blueprint $table) {
            $table->json('data')->nullable()->change();
        });
    }
};
