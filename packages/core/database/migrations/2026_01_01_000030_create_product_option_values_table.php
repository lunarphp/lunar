<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'product_option_values', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->ulid('public_id')->unique();
            $table->foreignId('product_option_id')->constrained($this->prefix.'product_options');
            $table->jsonb('name');
            $table->timestamps();
            $table->integer('position')->default(0)->index();
            $table->jsonb('meta')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'product_option_values');
    }
};
