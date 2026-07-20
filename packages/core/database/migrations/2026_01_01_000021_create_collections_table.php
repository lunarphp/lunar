<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'collections', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('collection_group_id')->constrained($this->prefix.'collection_groups');
            $table->nestedSet();
            $table->string('handle')->unique();
            $table->string('type')->default('static')->index();
            $table->jsonb('name');
            $table->jsonb('description')->nullable();
            $table->jsonb('short_description')->nullable();
            $table->jsonb('attribute_data')->nullable();
            $table->string('sort')->default('custom')->index();
            $table->string('status')->default('draft')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'collections');
    }
};
