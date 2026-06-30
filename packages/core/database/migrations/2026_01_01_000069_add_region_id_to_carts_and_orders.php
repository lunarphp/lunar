<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'carts', function (Blueprint $table) {
            $table->foreignId('region_id')->nullable()->constrained($this->prefix.'regions')->nullOnDelete();
        });

        Schema::table($this->prefix.'orders', function (Blueprint $table) {
            $table->foreignId('region_id')->nullable()->constrained($this->prefix.'regions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'carts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('region_id');
        });

        Schema::table($this->prefix.'orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('region_id');
        });
    }
};
