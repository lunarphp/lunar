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
        Schema::table($this->prefix.'brands', function (Blueprint $table) {
            $table->jsonb('attribute_data')->nullable()->change();
        });

        Schema::table($this->prefix.'collections', function (Blueprint $table) {
            $table->jsonb('attribute_data')->nullable()->change();
        });

        Schema::table($this->prefix.'customers', function (Blueprint $table) {
            $table->jsonb('attribute_data')->nullable()->change();
        });

        Schema::table($this->prefix.'customer_groups', function (Blueprint $table) {
            $table->jsonb('attribute_data')->nullable()->change();
        });

        Schema::table($this->prefix.'products', function (Blueprint $table) {
            $table->jsonb('attribute_data')->nullable()->change();
        });

        Schema::table($this->prefix.'product_variants', function (Blueprint $table) {
            $table->jsonb('attribute_data')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table($this->prefix.'brands', function (Blueprint $table) {
            $table->json('attribute_data')->nullable()->change();
        });

        Schema::table($this->prefix.'collections', function (Blueprint $table) {
            $table->json('attribute_data')->nullable()->change();
        });

        Schema::table($this->prefix.'customers', function (Blueprint $table) {
            $table->json('attribute_data')->nullable()->change();
        });

        Schema::table($this->prefix.'customer_groups', function (Blueprint $table) {
            $table->json('attribute_data')->nullable()->change();
        });

        Schema::table($this->prefix.'products', function (Blueprint $table) {
            $table->json('attribute_data')->nullable()->change();
        });

        Schema::table($this->prefix.'product_variants', function (Blueprint $table) {
            $table->json('attribute_data')->nullable()->change();
        });
    }
};
