<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * Reconcile databases that ran the v2 order-lines baseline before line-item
 * refunds added the refunded quantity rollup to that existing migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        $table = $this->prefix.'order_lines';

        if (! Schema::hasTable($table) || Schema::hasColumn($table, 'refunded_quantity')) {
            return;
        }

        Schema::table($table, function (Blueprint $table) {
            $table->unsignedInteger('refunded_quantity')
                ->default(0)
                ->comment('Rollup of refund_lines.quantity for this line');
        });
    }

    public function down(): void
    {
        // The flat baseline owns this column, so reconciliation must not remove it.
    }
};
