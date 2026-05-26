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
            $table->string('attribute_type')->index();
            $table->foreignId('attribute_group_id')->constrained($this->prefix.'attribute_groups');
            $table->integer('position')->index();
            $table->jsonb('name');
            $table->string('handle');
            $table->string('section')->nullable();
            $table->string('type')->index();
            $table->boolean('required');
            $table->string('default_value')->nullable();
            $table->jsonb('configuration');
            $table->boolean('system');
            $table->timestamps();
            $table->unique(['attribute_type', 'handle']);
            $table->boolean('searchable')->default(true)->index();
            $table->boolean('filterable')->default(false)->index();
            $table->string('validation_rules')->nullable();
            $table->jsonb('description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'attributes');
    }
};
