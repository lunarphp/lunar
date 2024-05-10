<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'brand_collection', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained($this->prefix.'brands');
            $table->foreignId('collection_id')->constrained($this->prefix.'collections');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'brand_collection');
    }
};
