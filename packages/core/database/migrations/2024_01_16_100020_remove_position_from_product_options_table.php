<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'product_options', function (Blueprint $table) {
            $table->dropIndex(['position']);
            $table->dropColumn('position');
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'product_options', function (Blueprint $table) {
            $table->smallInteger('position')->after('label');
        });
    }
};
