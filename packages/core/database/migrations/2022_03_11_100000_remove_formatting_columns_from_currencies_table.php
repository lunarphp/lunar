<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'currencies', function (Blueprint $table) {
            $table->dropColumn(['format', 'decimal_point', 'thousand_point']);
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'currencies', function (Blueprint $table) {
            $table->string('format')->nullable();
            $table->string('decimal_point')->nullable();
            $table->string('thousand_point')->nullable();
        });
    }
};
