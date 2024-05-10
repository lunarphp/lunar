<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'tax_classes', function (Blueprint $table) {
            $table->boolean('default')->after('name')->index()->default(false);
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'tax_classes', function (Blueprint $table) {
            $table->dropIndex(['default']);
            $table->dropColumn('default');
        });
    }
};
