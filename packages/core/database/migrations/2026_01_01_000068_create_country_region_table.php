<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'country_region', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained($this->prefix.'countries')->cascadeOnDelete();
            $table->foreignId('region_id')->constrained($this->prefix.'regions')->cascadeOnDelete();
            $table->unique(['country_id', 'region_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'country_region');
    }
};
