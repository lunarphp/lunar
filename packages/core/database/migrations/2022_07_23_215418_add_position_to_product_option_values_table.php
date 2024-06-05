<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'product_option_values', function (Blueprint $table) {
            $table->integer('position')->after('name')->default(0)->index();
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'product_option_values', function (Blueprint $table) {
            $table->dropIndex(['position']);
            $table->dropColumn('position');
        });
    }
};
