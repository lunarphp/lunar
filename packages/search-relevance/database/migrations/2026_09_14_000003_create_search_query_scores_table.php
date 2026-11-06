<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'search_query_scores', function (Blueprint $table) {
            $table->string('model_type');
            $table->string('normalised_query', 255);
            $table->unsignedBigInteger('product_id');
            $table->double('score');
            $table->double('relative');
            $table->integer('sessions');
            $table->string('version', 32);
            $table->timestamp('updated_at');
            $table->primary(['model_type', 'normalised_query', 'product_id']);
            $table->index(['version', 'normalised_query']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'search_query_scores');
    }
};
