<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'regions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('handle')->unique();
            $table->foreignId('channel_id')->constrained($this->prefix.'channels');
            $table->foreignId('currency_id')->constrained($this->prefix.'currencies');
            $table->foreignId('language_id')->constrained($this->prefix.'languages');
            $table->foreignId('tax_zone_id')->nullable()->constrained($this->prefix.'tax_zones')->nullOnDelete();
            $table->boolean('prices_inc_tax')->nullable();
            $table->boolean('default')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'regions');
    }
};
