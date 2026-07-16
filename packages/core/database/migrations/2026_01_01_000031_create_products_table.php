<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'products', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('product_type_id')->constrained($this->prefix.'product_types');
            $table->foreignId('brand_id')->nullable()->constrained($this->prefix.'brands');
            $table->string('status')->default('draft')->index();
            $table->jsonb('name');
            $table->jsonb('description')->nullable();
            $table->jsonb('short_description')->nullable();
            $table->jsonb('attribute_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'products');
    }
};
