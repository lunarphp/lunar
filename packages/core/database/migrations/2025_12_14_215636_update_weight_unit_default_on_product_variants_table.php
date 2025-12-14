<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix . 'product_variants', function (Blueprint $table) {
            $table->string('weight_unit')->default('kg')->nullable()->change();
        });

        DB::table($this->prefix . 'product_variants')
            ->whereNull('weight_unit')
            ->orWhere('weight_unit', 'mm')
            ->update(['weight_unit' => 'kg']);
    }

    public function down(): void
    {
        Schema::table($this->prefix . 'product_variants', function (Blueprint $table) {
            $table->string('weight_unit')->default('mm')->nullable()->change();
        });
    }
};
