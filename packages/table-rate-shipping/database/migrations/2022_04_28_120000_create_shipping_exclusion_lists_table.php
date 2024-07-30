<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'shipping_exclusion_lists', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'shipping_exclusion_lists');
    }
};
