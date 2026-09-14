<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'search_events', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('search_id')->constrained($this->prefix.'search_queries')->cascadeOnDelete();
            $table->unsignedBigInteger('product_id');
            $table->smallInteger('position');
            $table->string('type', 16);
            $table->string('source', 16);
            $table->string('session_id', 64);
            $table->timestamp('created_at');
            $table->index(['created_at', 'search_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'search_events');
    }
};
