<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $table = $this->prefix.'product_product_option';

        if (Schema::hasIndex($table, ['product_id', 'product_option_id'], 'unique')) {
            return;
        }

        $duplicateIds = DB::table($table.' as t')
            ->select('t.id')
            ->whereRaw('t.id > (
                select min(t2.id) from '.$table.' as t2
                where t2.product_id = t.product_id
                  and t2.product_option_id = t.product_option_id
            )')
            ->pluck('id');

        if ($duplicateIds->isNotEmpty()) {
            DB::table($table)->whereIn('id', $duplicateIds)->delete();
        }

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->unique(['product_id', 'product_option_id']);
        });
    }

    public function down(): void
    {
        $table = $this->prefix.'product_product_option';

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->dropUnique(['product_id', 'product_option_id']);
        });
    }
};
