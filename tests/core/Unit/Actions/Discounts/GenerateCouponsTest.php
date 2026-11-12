<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Lunar\Core\Contracts\CouponCodeGenerator;
use Lunar\Core\DataObjects\CouponBatch;
use Lunar\Core\Jobs\GenerateCouponsJob;
use Lunar\Core\Models\Coupon;
use Lunar\Core\Models\Discount;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

/**
 * Hands out the given codes in order, then repeats the last one.
 */
function fakeCouponCodes(array $codes): void
{
    app()->instance(CouponCodeGenerator::class, new class($codes) implements CouponCodeGenerator
    {
        public function __construct(public array $codes) {}

        public function generate(CouponBatch $batch): string
        {
            return count($this->codes) > 1 ? array_shift($this->codes) : $this->codes[0];
        }
    });
}

test('generates exactly the requested number of unique codes in one batch', function () {
    $discount = Discount::factory()->coupon()->create();
    $batch = CouponBatch::ofQuantity(2500, prefix: 'vip', maxUses: 2);

    $id = $discount->generateCoupons($batch);

    $coupons = $discount->coupons()->where('batch', $id)->get();

    expect($id)->toBe($batch->id)
        ->and($coupons)->toHaveCount(2500)
        ->and($coupons->pluck('code')->unique())->toHaveCount(2500)
        ->and($coupons->every(fn (Coupon $coupon) => str_starts_with($coupon->code, 'VIP')
            && $coupon->max_uses === 2
            && $coupon->uses === 0
            && $coupon->email === null))->toBeTrue();
});

test('issues one code per distinct email', function () {
    $discount = Discount::factory()->coupon()->create();

    $id = $discount->generateCoupons(CouponBatch::forEmails(['Jo@example.com', 'jo@example.com', 'sam@example.com']));

    expect($discount->coupons()->where('batch', $id)->orderBy('email')->pluck('email')->all())
        ->toBe(['jo@example.com', 'sam@example.com']);
});

test('replaces a code that collides with an existing one', function (string $kind) {
    $existing = Coupon::factory()->create(['code' => 'TAKEN']);
    fakeCouponCodes(['TAKEN', 'FRESH1', 'TAKEN', 'FRESH2']);
    $discount = Discount::factory()->coupon()->create();

    $discount->generateCoupons($kind === 'emails'
        ? CouponBatch::forEmails(['jo@example.com', 'sam@example.com'])
        : CouponBatch::ofQuantity(2));

    expect($discount->coupons()->orderBy('code')->pluck('code')->all())->toBe(['FRESH1', 'FRESH2'])
        ->and($existing->refresh()->discount_id)->not->toBe($discount->id);
})->with(['quantity', 'emails']);

test('running a batch again finishes it rather than duplicating it', function () {
    $discount = Discount::factory()->coupon()->create();
    $batch = CouponBatch::forEmails(['jo@example.com', 'sam@example.com']);
    Coupon::factory()->for($discount)->create(['email' => 'jo@example.com', 'batch' => $batch->id]);

    $discount->generateCoupons($batch);
    $discount->generateCoupons($batch);

    expect($discount->coupons()->where('batch', $batch->id)->orderBy('email')->pluck('email')->all())
        ->toBe(['jo@example.com', 'sam@example.com']);
});

test('gives up when every generated code is taken', function () {
    Coupon::factory()->create(['code' => 'TAKEN']);
    fakeCouponCodes(['TAKEN']);

    Discount::factory()->coupon()->create()->generateCoupons(CouponBatch::ofQuantity(1));
})->throws(RuntimeException::class);

test('the job generates the batch', function () {
    Queue::fake();
    $discount = Discount::factory()->coupon()->create();
    $batch = CouponBatch::ofQuantity(3);

    GenerateCouponsJob::dispatch($discount, $batch);

    Queue::assertPushed(GenerateCouponsJob::class, fn (GenerateCouponsJob $job) => $job->batch->id === $batch->id);

    (new GenerateCouponsJob($discount, $batch))->handle();

    expect($discount->coupons()->where('batch', $batch->id)->count())->toBe(3);
});
