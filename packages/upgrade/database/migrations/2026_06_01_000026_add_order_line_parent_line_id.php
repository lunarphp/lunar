<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step: add `order_lines.parent_line_id`.
 *
 * v2 records a bundle's parts as component order lines that point at the line
 * they belong to, and loads them through `OrderLine::components()`. The ledger
 * rewrite marks the `order_lines` baseline as run, so v1 databases never
 * receive the column from it. v1 orders have no component lines, so every
 * existing line keeps a null parent.
 *
 * Guarded so re-runs and already-v2 databases are no-ops. One-way, no down().
 */
return new class extends Migration
{
    public function up(): void
    {
        $table = $this->prefix.'order_lines';

        if (! Schema::hasTable($table) || Schema::hasColumn($table, 'parent_line_id')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($table) {
            $blueprint->foreignId('parent_line_id')
                ->nullable()
                ->after('order_id')
                ->index()
                ->constrained($table)
                ->cascadeOnDelete()
                ->comment('Set on a component line that belongs to another line (a bundle part); never priced');
        });
    }
};
