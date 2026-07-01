<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'collection_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->ulid('public_id')->unique();
            $table->string('name');
            $table->string('handle')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'collection_groups');
    }
};
