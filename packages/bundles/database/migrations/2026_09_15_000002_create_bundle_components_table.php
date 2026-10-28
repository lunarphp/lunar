<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'bundle_components', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('bundle_id')->constrained($this->prefix.'bundles')->cascadeOnDelete();
            $table->foreignId('bundle_group_id')->nullable()->constrained($this->prefix.'bundle_groups')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained($this->prefix.'product_variants')->cascadeOnDelete();
            $table->smallInteger('quantity')->default(1);
            $table->boolean('default')->default(false);
            $table->smallInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['bundle_id', 'bundle_group_id', 'product_variant_id'], 'bundle_components_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'bundle_components');
    }
};
