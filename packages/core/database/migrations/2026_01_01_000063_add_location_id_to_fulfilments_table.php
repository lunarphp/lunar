<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'fulfilments', function (Blueprint $table) {
            $table->foreignId('location_id')
                ->nullable()
                ->after('order_id')
                ->constrained($this->prefix.'locations')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'fulfilments', function (Blueprint $table) {
            if ($this->canDropForeignKeys()) {
                $table->dropConstrainedForeignId('location_id');
            }
        });
    }
};
