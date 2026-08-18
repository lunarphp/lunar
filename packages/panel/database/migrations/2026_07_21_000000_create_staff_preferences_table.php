<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'staff_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained($this->prefix.'staff')->cascadeOnDelete();
            $table->string('key');
            $table->json('value');
            $table->timestamps();

            $table->unique(['staff_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'staff_preferences');
    }
};
