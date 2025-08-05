<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table($this->prefix.'addresses', function (Blueprint $table) {
            $table->string('delivery_instructions', 1000)->nullable()->change();
        });

        Schema::table($this->prefix.'cart_addresses', function (Blueprint $table) {
            $table->string('delivery_instructions', 1000)->nullable()->change();
        });

        Schema::table($this->prefix.'order_addresses', function (Blueprint $table) {
            $table->string('delivery_instructions', 1000)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table($this->prefix.'addresses', function (Blueprint $table) {
            $table->string('delivery_instructions')->nullable()->change();
        });

        Schema::table($this->prefix.'cart_addresses', function (Blueprint $table) {
            $table->string('delivery_instructions')->nullable()->change();
        });

        Schema::table($this->prefix.'order_addresses', function (Blueprint $table) {
            $table->string('delivery_instructions')->nullable()->change();
        });
    }
};
