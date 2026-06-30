<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'tax_zones', function (Blueprint $table) {
            $table->dropColumn('price_display');
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'tax_zones', function (Blueprint $table) {
            $table->string('price_display')->nullable();
        });
    }
};
