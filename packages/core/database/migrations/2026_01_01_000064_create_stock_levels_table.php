<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'stock_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained($this->prefix.'product_variants')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained($this->prefix.'locations')->restrictOnDelete();
            $table->integer('on_hand')->default(0);
            $table->integer('incoming')->default(0);
            $table->integer('committed')->default(0);
            $table->integer('unavailable')->default(0);
            $table->jsonb('meta')->nullable();
            $table->timestamps();
            $table->unique(['product_variant_id', 'location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'stock_levels');
    }
};
