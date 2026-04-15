<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'cart_addresses', function (Blueprint $table) {
            $table->string('tax_identifier')->after('company_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'cart_addresses', function (Blueprint $table) {
            $table->dropColumn('tax_identifier');
        });
    }
};
