<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'attribute_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained($this->prefix.'attributes')->cascadeOnDelete();
            $table->string('model_type')->index();
            $table->unique(['attribute_id', 'model_type']);
        });

        Schema::create($this->prefix.'product_type_attribute', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_type_id')->constrained($this->prefix.'product_types')->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained($this->prefix.'attributes')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['product_type_id', 'attribute_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'product_type_attribute');
        Schema::dropIfExists($this->prefix.'attribute_models');
    }
};
