<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * Closes the orders <-> carts foreign-key cycle.
 *
 * Both tables reference each other (`orders.cart_id` → carts, `carts.order_id`
 * → orders), so one side can't be added inside its own create migration.
 * `carts.order_id` is declared inline in `create_carts_table`; the matching
 * `orders.cart_id` FK is added here, once both tables exist.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'orders', function (Blueprint $table) {
            $table->foreign('cart_id')
                ->references('id')
                ->on($this->prefix.'carts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'orders', function (Blueprint $table) {
            $table->dropForeign(['cart_id']);
        });
    }
};
