<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'bundleables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bundle_id')->constrained($this->prefix.'bundles');
            $table->morphs('bundleable');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'bundleables');
    }
};
