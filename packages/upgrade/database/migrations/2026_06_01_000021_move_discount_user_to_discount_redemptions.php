<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step (spec discount-coupons): replace `discount_user`
 * with `discount_redemptions`.
 *
 * v2 records one redemption per discount per placed order. v1 recorded only
 * the signed-in user, so each `discount_user` row becomes a redemption with its
 * `user_id`, `redeemed_at` from `created_at`, and no order, cart or email.
 *
 * The ledger rewrite marks the baseline as run, so the table is created here
 * (mirroring the baseline). The `discount_user` table is the v1 signal: absent
 * means already v2. There is no `down()`: upgrade data migrations are one-way.
 */
return new class extends Migration
{
    public function up(): void
    {
        $discountUser = $this->prefix.'discount_user';
        $redemptions = $this->prefix.'discount_redemptions';

        if (! Schema::hasTable($discountUser)) {
            return;
        }

        if (! Schema::hasTable($redemptions)) {
            Schema::create($redemptions, function (Blueprint $table) {
                $table->id();
                $table->foreignId('discount_id')->constrained($this->prefix.'discounts')->cascadeOnDelete();
                $table->foreignId('coupon_id')->nullable()->constrained($this->prefix.'coupons')->nullOnDelete();
                $table->foreignId('order_id')->nullable()->constrained($this->prefix.'orders')->nullOnDelete();
                $table->foreignId('cart_id')->nullable()->constrained($this->prefix.'carts')->nullOnDelete();
                $table->userForeignKey(nullable: true);
                $table->string('email')->nullable();
                $table->timestamp('redeemed_at')->index();

                $table->unique(['discount_id', 'order_id']);
                $table->index(['discount_id', 'email']);
            });
        }

        // Rows without an order can only come from an earlier run that stopped before the drop.
        DB::table($redemptions)->whereNull('order_id')->delete();

        DB::table($redemptions)->insertUsing(
            ['discount_id', 'user_id', 'redeemed_at'],
            DB::table($discountUser)
                ->select(['discount_id', 'user_id', DB::raw('coalesce(created_at, CURRENT_TIMESTAMP)')])
                ->orderBy('id'),
        );

        Schema::drop($discountUser);
    }
};
