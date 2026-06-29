<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained($this->prefix.'product_variants')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained($this->prefix.'locations')->restrictOnDelete();
            $table->integer('quantity'); // signed delta applied to on_hand
            $table->string('type')->index();
            $table->nullableMorphs('source');
            $table->string('note')->nullable();
            $table->nullableMorphs('causer');
            $table->timestamp('created_at')->nullable(); // append-only ledger; no updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'stock_movements');
    }
};
