<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'cart_lines', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->change();
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'cart_lines', function (Blueprint $table) {
            $table->smallInteger('quantity')->unsigned()->change();
        });
    }
};
