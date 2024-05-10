<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'customer_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained($this->prefix.'customers');
            $table->userForeignKey();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'customer_user');
    }
};
