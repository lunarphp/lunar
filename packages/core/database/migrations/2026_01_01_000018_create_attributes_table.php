<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'attributes', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('attribute_group_id')->nullable()->constrained($this->prefix.'attribute_groups')->nullOnDelete();
            $table->string('name');
            $table->string('handle')->unique();
            $table->string('type')->index();
            $table->json('configuration')->nullable();
            $table->integer('position')->default(1)->index();
            $table->boolean('required')->default(false);
            $table->json('validation_rules')->nullable();
            $table->boolean('searchable')->default(false)->index();
            $table->boolean('filterable')->default(false)->index();
            $table->boolean('system')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'attributes');
    }
};
