<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Lunar\Core\Contracts\CouponCodeGenerator;
use Lunar\Core\DataObjects\CouponBatch;
use Lunar\Core\Jobs\GenerateCouponsJob;
use Lunar\Core\Models\Coupon;
use Lunar\Core\Models\Discount;
use Lunar\Tests\Core\TestCase;

/*
 * Sets the default for lunar.discounts.coupons.queue_threshold. Outside the
 * core suite's directories, so it only runs when asked for:
 *
 *   vendor/bin/pest tests/core/Benchmarks
 *   DB_DRIVER=mysql DB_USERNAME=root vendor/bin/pest tests/core/Benchmarks
 *   DB_DRIVER=pgsql DB_USERNAME=<user> vendor/bin/pest tests/core/Benchmarks
 *
 * DatabaseMigrations rather than RefreshDatabase, so inserts commit one by one
 * as they do in a request, not inside a test transaction.
 */
uses(TestCase::class)->group('bench');
uses(DatabaseMigrations::class);

test('generating coupons into a table that already holds many codes', function () {
    config(['queue.default' => 'sync']);

    $existing = (int) env('BENCH_EXISTING_CODES', 200_000);
    $seed = Discount::factory()->coupon()->create();
    $seedBatch = CouponBatch::ofQuantity(1);
    $now = now();

    for ($i = 0; $i < $existing; $i += 1000) {
        Coupon::query()->insert(array_map(fn (int $n) => [
            'discount_id' => $seed->id,
            'code' => sprintf('SEED%08d', $n),
            'uses' => 0,
            'max_uses' => 1,
            'batch' => $seedBatch->id,
            'created_at' => $now,
            'updated_at' => $now,
        ], range($i, min($i + 1000, $existing) - 1)));
    }

    $generator = app(CouponCodeGenerator::class);
    $driver = Coupon::query()->getConnection()->getDriverName();

    foreach (['quantity', 'emails'] as $kind) {
        foreach ([1_000, 5_000, 10_000, 50_000] as $quantity) {
            $discount = Discount::factory()->coupon()->create();
            $batch = $kind === 'emails'
                ? CouponBatch::forEmails(array_map(fn (int $n) => "customer{$n}@example.com", range(1, $quantity)))
                : CouponBatch::ofQuantity($quantity);

            $t0 = hrtime(true);
            for ($i = 0; $i < $quantity; $i++) {
                $generator->generate($batch);
            }
            $generateMs = (hrtime(true) - $t0) / 1e6;

            $t0 = hrtime(true);
            GenerateCouponsJob::dispatch($discount, $batch);
            $totalMs = (hrtime(true) - $t0) / 1e6;

            expect($discount->coupons()->count())->toBe($quantity);

            fwrite(STDOUT, sprintf(
                "\n[bench] driver=%s existing=%d %s=%d total=%.0fms codes-only=%.0fms per-1k=%.0fms",
                $driver, $existing, $kind, $quantity, $totalMs, $generateMs, $totalMs / ($quantity / 1000),
            ));
        }
    }

    fwrite(STDOUT, "\n");
});
