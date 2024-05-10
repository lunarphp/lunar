<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'discounts', function (Blueprint $table) {
            $table->mediumInteger('max_uses_per_user')->unsigned()->nullable()->after('max_uses');
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'discounts', function (Blueprint $table) {
            $table->dropColumn('max_uses_per_user');
        });
    }
};
