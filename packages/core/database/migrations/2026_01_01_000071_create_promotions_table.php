<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'promotions', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->jsonb('name');
            $table->jsonb('description')->nullable();
            $table->string('handle')->unique();
            $table->dateTime('starts_at')->nullable()->index();
            $table->dateTime('ends_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'promotions');
    }
};
