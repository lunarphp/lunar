<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'edit_drafts', function (Blueprint $table) {
            $table->id();
            $table->morphs('draftable');
            $table->foreignId('staff_id')->constrained($this->prefix.'staff')->cascadeOnDelete();
            $table->json('data');
            $table->json('base_snapshot');
            $table->timestamps();

            $table->unique(['draftable_type', 'draftable_id', 'staff_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'edit_drafts');
    }
};
