<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * Columns that were added by editing the original create migrations during
 * the 2.0 alpha. A database migrated before each edit never received them,
 * since its create migration had already run, so this adds whichever are
 * missing and leaves a database that has them untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn($this->prefix.'product_options', 'type')) {
            Schema::table($this->prefix.'product_options', function (Blueprint $table) {
                $table->string('type')->default('text')->index();
            });
        }

        if (! Schema::hasColumn($this->prefix.'attributes', 'validation_rules')) {
            Schema::table($this->prefix.'attributes', function (Blueprint $table) {
                $table->json('validation_rules')->nullable();
            });
        }

        if (! Schema::hasColumn($this->prefix.'order_lines', 'refunded_quantity')) {
            Schema::table($this->prefix.'order_lines', function (Blueprint $table) {
                $table->unsignedInteger('refunded_quantity')->default(0)->comment('Rollup of refund_lines.quantity for this line');
            });

            $this->backfillRefundedQuantities();
        }
    }

    public function down(): void
    {
        // Nothing to reverse: on most databases these columns belong to the
        // create migrations, and dropping them here would break those.
    }

    /**
     * Refunds recorded before the column existed still have their refund
     * lines, so the rollup is rebuilt from them.
     */
    protected function backfillRefundedQuantities(): void
    {
        DB::table($this->prefix.'refund_lines')
            ->select('order_line_id', DB::raw('SUM(quantity) as refunded'))
            ->groupBy('order_line_id')
            ->orderBy('order_line_id')
            ->each(function (object $row): void {
                DB::table($this->prefix.'order_lines')
                    ->where('id', $row->order_line_id)
                    ->update(['refunded_quantity' => (int) $row->refunded]);
            });
    }
};
