<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'tax_classes', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->string('name');
            $table->timestamps();
            $table->boolean('default')->index()->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'tax_classes');
    }
};
