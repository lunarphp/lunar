<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'regions', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->string('name');
            $table->string('handle')->unique();
            $table->foreignId('channel_id')->constrained($this->prefix.'channels');
            $table->foreignId('currency_id')->constrained($this->prefix.'currencies');
            $table->foreignId('language_id')->constrained($this->prefix.'languages');
            $table->foreignId('tax_zone_id')->nullable()->constrained($this->prefix.'tax_zones')->nullOnDelete();
            $table->boolean('prices_inc_tax')->nullable();
            $table->boolean('default')->default(false)->index();
            $table->timestamps();
        });

        // Carts and orders are created before regions, so their region keys are constrained here.
        foreach (['carts', 'orders'] as $name) {
            Schema::table($this->prefix.$name, function (Blueprint $table) {
                $table->foreign('region_id')->references('id')->on($this->prefix.'regions')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (['carts', 'orders'] as $name) {
            Schema::table($this->prefix.$name, function (Blueprint $table) {
                $table->dropForeign(['region_id']);
            });
        }

        Schema::dropIfExists($this->prefix.'regions');
    }
};
