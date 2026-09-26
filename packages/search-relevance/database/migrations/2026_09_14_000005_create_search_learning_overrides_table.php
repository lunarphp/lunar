<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'search_learning_overrides', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');
            $table->string('normalised_query');
            // Null for a query-wide `reset`; the product for an `exclude`.
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('type', 16);
            $table->timestamp('created_at');

            // Named: the generated name exceeds MySQL's 64-character limit.
            $table->index(['model_type', 'normalised_query', 'type'], $this->prefix.'learning_overrides_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'search_learning_overrides');
    }
};
