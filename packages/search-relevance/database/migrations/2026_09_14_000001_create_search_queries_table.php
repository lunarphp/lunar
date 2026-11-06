<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'search_queries', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('model_type');
            $table->text('raw_query');
            $table->string('normalised_query', 255)->index();
            $table->char('filters_hash', 32);
            $table->string('session_id', 64)->index();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('version', 32)->index();
            $table->string('mode', 8);
            $table->integer('result_count');
            $table->json('shown');
            $table->json('ranked')->nullable();
            $table->json('features')->nullable();
            $table->timestamp('created_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'search_queries');
    }
};
