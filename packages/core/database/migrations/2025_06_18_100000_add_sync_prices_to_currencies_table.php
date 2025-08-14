<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'currencies', function (Blueprint $table) {
            $table->boolean('sync_prices')->after('default')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'currencies', function (Blueprint $table) {
            $table->dropColumn('sync_prices');
        });
    }
};
