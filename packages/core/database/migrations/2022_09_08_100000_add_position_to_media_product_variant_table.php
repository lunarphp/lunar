<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'media_product_variant', function (Blueprint $table) {
            $table->smallInteger('position')->after('primary')->default(1)->index();
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'media_product_variant', function (Blueprint $table) {
            $table->dropIndex(['position']);
            $table->dropColumn('position');
        });
    }
};
