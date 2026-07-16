<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'staff', function (Blueprint $table) {
            $table->string('preferred_locale')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'staff', function (Blueprint $table) {
            $table->dropColumn('preferred_locale');
        });
    }
};
