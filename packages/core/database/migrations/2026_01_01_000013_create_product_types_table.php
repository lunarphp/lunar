<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'product_types', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->string('name');
            $table->string('handle')->unique();
            $table->string('status')->default('active')->index();
            $table->text('description')->nullable();
            // Constrained in 2026_01_01_000071 — tax_classes is created later.
            $table->foreignId('default_tax_class_id')->nullable();
            $table->timestamps();
            $table->jsonb('attribute_data')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'product_types');
    }
};
