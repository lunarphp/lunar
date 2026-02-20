<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'product_options', function (Blueprint $table) {
            $table->jsonb('meta')->nullable()->after('shared');
        });

        Schema::table($this->prefix.'product_option_values', function (Blueprint $table) {
            $table->jsonb('meta')->nullable()->after('position');
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'product_options', function (Blueprint $table) {
            $table->dropColumn('meta');
        });

        Schema::table($this->prefix.'product_option_values', function (Blueprint $table) {
            $table->dropColumn('meta');
        });
    }
};
