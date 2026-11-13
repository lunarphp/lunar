<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'carts', function (Blueprint $table) {
            $table->foreignId('pickup_location_id')->nullable()->constrained($this->prefix.'locations')->nullOnDelete();
        });

        Schema::table($this->prefix.'orders', function (Blueprint $table) {
            $table->foreignId('pickup_location_id')->nullable()->constrained($this->prefix.'locations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'carts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pickup_location_id');
        });

        Schema::table($this->prefix.'orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pickup_location_id');
        });
    }
};
