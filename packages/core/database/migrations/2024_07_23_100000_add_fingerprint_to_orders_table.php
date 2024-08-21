<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'orders', function (Blueprint $table) {
            $table->string('fingerprint')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'orders', function (Blueprint $table) {
            $table->dropIndex(['fingerprint']);
        });
        Schema::table($this->prefix.'orders', function (Blueprint $table) {
            $table->dropColumn('fingerprint');
        });
    }
};
