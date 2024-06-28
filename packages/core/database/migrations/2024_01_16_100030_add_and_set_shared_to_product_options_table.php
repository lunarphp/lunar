<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'product_options', function (Blueprint $table) {
            $table->boolean('shared')->after('handle')->default(false)->index();
        });

        \Lunar\Facades\DB::table($this->prefix.'product_options')->update([
            'shared' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table($this->prefix.'product_options', function (Blueprint $table) {
            $table->dropIndex(['shared']);
            $table->dropColumn('shared');
        });
    }
};
