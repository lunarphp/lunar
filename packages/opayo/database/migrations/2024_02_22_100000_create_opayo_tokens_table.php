<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'opayo_tokens', function (Blueprint $table) {
            $table->id();
            $table->userForeignKey();
            $table->string('card_type')->index();
            $table->string('last_four');
            $table->string('token');
            $table->string('auth_code')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'opayo_tokens');
    }
};
