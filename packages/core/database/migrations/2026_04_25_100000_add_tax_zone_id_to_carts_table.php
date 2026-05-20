<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'carts', function (Blueprint $table) {
            $table->foreignId('tax_zone_id')->after('channel_id')
                ->nullable()
                ->constrained($this->prefix.'tax_zones')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'carts', function (Blueprint $table) {
            if ($this->canDropForeignKeys()) {
                $table->dropForeign(['tax_zone_id']);
            }
            $table->dropColumn('tax_zone_id');
        });
    }
};
