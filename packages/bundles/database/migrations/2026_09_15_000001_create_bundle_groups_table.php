<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'bundle_groups', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('bundle_id')->constrained($this->prefix.'bundles')->cascadeOnDelete();
            $table->json('name');
            $table->smallInteger('min_selections')->default(1);
            $table->smallInteger('max_selections')->default(1);
            $table->smallInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'bundle_groups');
    }
};
