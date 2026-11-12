<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step (spec discount-coupons): move `discounts.coupon`
 * into `coupons`.
 *
 * v1 held at most one code per discount. Each becomes a `coupons` row that
 * carries the discount's `uses` and `max_uses`, the discount's `redemption` is
 * set to `coupon`, and every other discount is `automatic`.
 *
 * The ledger rewrite marks the baseline as run, so the table is created here
 * (mirroring the baseline). The `discounts.coupon` column is the v1 signal:
 * absent means already v2. Every part converges on a re-run, because codes are
 * unique and the column is dropped last. One-way, no down().
 */
return new class extends Migration
{
    public function up(): void
    {
        $discounts = $this->prefix.'discounts';
        $coupons = $this->prefix.'coupons';

        if (! Schema::hasTable($discounts) || ! Schema::hasColumn($discounts, 'coupon')) {
            return;
        }

        if (! Schema::hasTable($coupons)) {
            Schema::create($coupons, function (Blueprint $table) {
                $table->id();
                $table->foreignId('discount_id')->constrained($this->prefix.'discounts')->cascadeOnDelete();
                $table->string('code')->unique();
                $table->unsignedInteger('uses')->default(0);
                $table->unsignedInteger('max_uses')->nullable();
                $table->string('email')->nullable()->index();
                $table->ulid('batch')->nullable()->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn($discounts, 'redemption')) {
            Schema::table($discounts, function (Blueprint $table) {
                $table->string('redemption')->default('automatic')->index();
            });
        }

        DB::table($discounts)
            ->whereNotNull('coupon')
            ->where('coupon', '!=', '')
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($coupons) {
                DB::table($coupons)->insertOrIgnore($rows->map(fn ($row) => [
                    'discount_id' => $row->id,
                    'code' => Str::upper($row->coupon),
                    'uses' => (int) $row->uses,
                    'max_uses' => $row->max_uses,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->all());
            });

        DB::table($discounts)->update([
            'redemption' => DB::raw("case when coupon is null or coupon = '' then 'automatic' else 'coupon' end"),
        ]);

        if (Schema::hasIndex($discounts, ['coupon'], 'unique')) {
            Schema::table($discounts, function (Blueprint $table) {
                $table->dropUnique(['coupon']);
            });
        }

        if (Schema::hasIndex($discounts, ['coupon'])) {
            Schema::table($discounts, function (Blueprint $table) {
                $table->dropIndex(['coupon']);
            });
        }

        Schema::table($discounts, function (Blueprint $table) {
            $table->dropColumn('coupon');
        });
    }
};
