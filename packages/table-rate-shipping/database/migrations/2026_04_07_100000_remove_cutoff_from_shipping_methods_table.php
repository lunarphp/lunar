<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'shipping_methods', function (Blueprint $table) {
            $table->dropColumn('cutoff');
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'shipping_methods', function (Blueprint $table) {
            $table->time('cutoff')->nullable()->after('stock_available');
        });
    }
};
