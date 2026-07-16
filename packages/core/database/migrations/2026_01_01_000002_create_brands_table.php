<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'brands', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->string('name');
            $table->jsonb('description')->nullable();
            $table->jsonb('short_description')->nullable();
            $table->timestamps();
            $table->jsonb('attribute_data')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'brands');
    }
};
