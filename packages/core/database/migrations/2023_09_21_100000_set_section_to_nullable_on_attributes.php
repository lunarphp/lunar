<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'attributes', function (Blueprint $table) {
            $table->string('section')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'attributes', function (Blueprint $table) {
            $table->string('section')->nullable(false)->change();
        });
    }
};
