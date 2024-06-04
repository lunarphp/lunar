<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('iso3')->unique();
            $table->string('iso2')->unique()->nullable();
            $table->string('phonecode');
            $table->string('capital')->nullable();
            $table->string('currency');
            $table->string('native')->nullable();
            $table->string('emoji');
            $table->string('emoji_u');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'countries');
    }
};
