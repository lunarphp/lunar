<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'product_variants', function (Blueprint $table) {
            $table->string('weight_unit')->default('kg')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'product_variants', function (Blueprint $table) {
            $table->string('weight_unit')->default('mm')->nullable()->change();
        });
    }
};
