<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'shipping_methods', function (Blueprint $table) {
            $table->decimal('min_weight', 10, 3)->nullable()->after('stock_available');
            $table->decimal('max_weight', 10, 3)->nullable()->after('min_weight');
            $table->string('weight_unit')->nullable()->after('max_weight');
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'shipping_methods', function (Blueprint $table) {
            $table->dropColumn(['min_weight', 'max_weight', 'weight_unit']);
        });
    }
};
