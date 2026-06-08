<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'fulfilments', function (Blueprint $table) {
            // Required: a fulfilment always ships from a location. (Added here
            // rather than in the fulfilments baseline so it orders after the
            // locations table for the foreign key.)
            $table->foreignId('location_id')
                ->after('order_id')
                ->constrained($this->prefix.'locations')
                ->restrictOnDelete();
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
