<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step: add `payment_terms` to carts and orders.
 *
 * v2 records when an order is paid: at checkout, on account, on pickup or on
 * delivery. Every v1 order was paid at checkout, which the orders column
 * defaults to, and order creation treats a cart without terms as checkout, so
 * no backfill is needed.
 *
 * Guarded so re-runs and already-v2 databases are no-ops. One-way, no down().
 */
return new class extends Migration
{
    public function up(): void
    {
        $carts = $this->prefix.'carts';

        if (Schema::hasTable($carts) && ! Schema::hasColumn($carts, 'payment_terms')) {
            Schema::table($carts, function (Blueprint $table) {
                $table->string('payment_terms')->nullable();
            });
        }

        $orders = $this->prefix.'orders';

        if (Schema::hasTable($orders) && ! Schema::hasColumn($orders, 'payment_terms')) {
            Schema::table($orders, function (Blueprint $table) {
                $table->string('payment_terms')->default('checkout');
            });
        }
    }
};
