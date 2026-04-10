<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'staff', function (Blueprint $table) {
            $table->renameColumn('two_factor_secret', 'app_authentication_secret');
            $table->renameColumn('two_factor_recovery_codes', 'app_authentication_recovery_codes');
            $table->dropColumn('two_factor_confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'staff', function (Blueprint $table) {
            $table->renameColumn('app_authentication_secret', 'two_factor_secret');
            $table->renameColumn('app_authentication_recovery_codes', 'two_factor_recovery_codes');
            $table->timestamp('two_factor_confirmed_at')->nullable();
        });
    }
};
