<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $table = $this->prefix.'product_product_option';

        if (! Schema::hasIndex($table, ['product_id', 'product_option_id'], 'unique')) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->unique(['product_id', 'product_option_id']);
            });
        }
    }

    public function down(): void
    {
        $table = $this->prefix.'product_product_option';

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->dropUnique(['product_id', 'product_option_id']);
        });
    }
};
