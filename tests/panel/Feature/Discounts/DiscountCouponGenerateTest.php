<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Contracts\CouponCodeGenerator;
use Lunar\Core\DataObjects\CouponBatch;
use Lunar\Core\Jobs\GenerateCouponsJob;
use Lunar\Core\Models\Coupon;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\DiscountRedemption;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $this->discount = Discount::factory()->coupon()->create(['handle' => 'spring-sale']);
});

function generateCoupons(array $input): TestResponse
{
    return test()->post(route('panel.discounts.coupons.generate', test()->discount), $input + [
        'source' => 'quantity',
        'length' => 10,
        'max_uses' => 1,
    ]);
}

it('generates a quantity of codes in the request', function () {
    generateCoupons(['quantity' => 25, 'prefix' => 'spr', 'length' => 8, 'max_uses' => 2])
        ->assertRedirect()
        ->assertSessionHas('success');

    $coupons = $this->discount->coupons()->get();

    expect($coupons)->toHaveCount(25)
        ->and($coupons->pluck('batch')->unique())->toHaveCount(1)
        ->and($coupons->every(fn (Coupon $coupon) => preg_match('/^SPR[2-9A-HJKMNP-Z]{8}$/', $coupon->code) === 1 && $coupon->max_uses === 2))->toBeTrue();
});

it('generates unlimited codes when uses per code is blank', function () {
    generateCoupons(['quantity' => 2, 'max_uses' => ''])->assertRedirect();

    expect($this->discount->coupons()->pluck('max_uses')->all())->toBe([null, null]);
});

it('generates one code per pasted and uploaded email', function () {
    $csv = UploadedFile::fake()->createWithContent('customers.csv', "name,email\nJo Bloggs,jo@example.com\nSam,sam@example.com\n");

    generateCoupons([
        'source' => 'emails',
        'emails' => "Jo@Example.com, ana@example.com\nana@example.com",
        'emails_file' => $csv,
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect($this->discount->coupons()->orderBy('email')->pluck('email')->all())
        ->toBe(['ana@example.com', 'jo@example.com', 'sam@example.com']);
});

it('rejects invalid input', function (array $input, string $field) {
    generateCoupons($input)->assertSessionHasErrors($field);

    expect($this->discount->coupons()->count())->toBe(0);
})->with([
    'no quantity' => [['quantity' => ''], 'quantity'],
    'too short' => [['quantity' => 5, 'length' => 4], 'length'],
    'bad prefix' => [['quantity' => 5, 'prefix' => 'no spaces'], 'prefix'],
    'no emails' => [['source' => 'emails', 'emails' => 'name, other'], 'emails'],
    'invalid email' => [['source' => 'emails', 'emails' => 'jo@example.com, not@valid@'], 'emails'],
]);

it('queues a batch above the threshold and shows it as generating until done', function () {
    config(['lunar.discounts.coupons.queue_threshold' => 10]);
    Bus::fake();

    generateCoupons(['quantity' => 11])->assertRedirect()->assertSessionHas('success');

    expect($this->discount->coupons()->count())->toBe(0);

    $job = null;
    Bus::assertChained([function (GenerateCouponsJob $queued) use (&$job) {
        $job = $queued;

        return $queued->batch->quantity === 11;
    }]);

    $this->get(route('panel.discounts.edit', $this->discount))
        ->assertInertia(fn (Assert $page) => $page
            ->where('couponQueueThreshold', 10)
            ->has('couponGenerations', 1)
            ->where('couponGenerations.0.batch', $job->batch->id)
            ->where('couponGenerations.0.quantity', 11)
            ->where('couponGenerations.0.generated', 0)
            ->where('couponGenerations.0.failed', false)
        );

    $job->handle();

    $this->get(route('panel.discounts.edit', $this->discount))
        ->assertInertia(fn (Assert $page) => $page->has('couponGenerations', 0));

    expect($this->discount->coupons()->count())->toBe(11);
});

it('shows a queued batch that failed', function () {
    config(['lunar.discounts.coupons.queue_threshold' => 1, 'queue.default' => 'sync']);
    app()->instance(CouponCodeGenerator::class, new class implements CouponCodeGenerator
    {
        public function generate(CouponBatch $batch): string
        {
            throw new RuntimeException('Generator down');
        }
    });

    $this->withoutExceptionHandling();

    expect(fn () => generateCoupons(['quantity' => 2]))->toThrow(RuntimeException::class, 'Generator down');

    $this->get(route('panel.discounts.edit', $this->discount))
        ->assertInertia(fn (Assert $page) => $page
            ->has('couponGenerations', 1)
            ->where('couponGenerations.0.failed', true)
            ->where('couponGenerations.0.generated', 0)
        );
});

it('exports every code, or one batch, as CSV', function () {
    $spring = $this->discount->generateCoupons(CouponBatch::forEmails(['jo@example.com']));
    $other = $this->discount->generateCoupons(CouponBatch::ofQuantity(1));
    $jo = $this->discount->coupons()->where('batch', $spring)->sole();
    $jo->update(['uses' => 1]);
    DiscountRedemption::factory()->for($this->discount)->create(['coupon_id' => $jo->id, 'redeemed_at' => '2026-03-01 10:00:00']);
    Coupon::factory()->for(Discount::factory()->coupon())->create(['code' => 'ELSEWHERE']);

    $all = $this->get(route('panel.discounts.coupons.export', $this->discount));

    $all->assertOk()
        ->assertDownload('spring-sale-coupons.csv')
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

    $rows = array_map('str_getcsv', array_filter(explode("\n", $all->streamedContent())));

    expect($rows[0])->toBe(['code', 'email', 'uses', 'redeemed_at'])
        ->and($rows)->toHaveCount(3)
        ->and(collect($rows)->pluck(0))->not->toContain('ELSEWHERE')
        ->and($rows[1])->toBe([$jo->code, 'jo@example.com', '1', '2026-03-01 10:00:00']);

    $batch = $this->get(route('panel.discounts.coupons.export', [$this->discount, 'batch' => $other]));

    $batch->assertDownload('spring-sale-coupons-'.strtolower($other).'.csv');

    $rows = array_map('str_getcsv', array_filter(explode("\n", $batch->streamedContent())));

    expect($rows)->toHaveCount(2)
        ->and($rows[1][0])->toBe($this->discount->coupons()->where('batch', $other)->sole()->code)
        ->and($rows[1][1])->toBe('');
});

it('links each generated code to its batch export', function () {
    $batch = $this->discount->generateCoupons(CouponBatch::ofQuantity(1));

    $this->get(route('panel.discounts.edit', $this->discount))
        ->assertInertia(fn (Assert $page) => $page
            ->where('coupons.data.0._actions.exportBatch', route('panel.discounts.coupons.export', [$this->discount, 'batch' => $batch]))
            ->where('urls.couponGenerate', route('panel.discounts.coupons.generate', $this->discount))
            ->where('urls.couponExport', route('panel.discounts.coupons.export', $this->discount))
        );
});

it('keeps generation and export behind the discounts permission', function () {
    $this->actingAs(Staff::factory()->create(['admin' => false]), 'staff');

    generateCoupons(['quantity' => 1])->assertForbidden();
    $this->get(route('panel.discounts.coupons.export', $this->discount))->assertForbidden();
});
