<?php

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\DiscountResource\Pages\EditDiscount;
use Lunar\Core\Models\Coupon;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\DiscountRedemption;
use Lunar\Filament\RelationManagers\Discount\CouponRelationManager;
use Lunar\Tests\Admin\Unit\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.discount');

beforeEach(function () {
    $this->asStaff(admin: true);
});

function couponManager(Discount $discount)
{
    return Livewire::test(CouponRelationManager::class, [
        'ownerRecord' => $discount,
        'pageClass' => EditDiscount::class,
    ]);
}

it('lists the discount coupons', function () {
    $discount = Discount::factory()->coupon('ALPHA', ['email' => 'a@example.com'])->create();
    $discount->coupons()->create(['code' => 'BRAVO', 'max_uses' => 5]);
    Coupon::factory()->create(['code' => 'OTHER']);

    couponManager($discount)
        ->assertCanSeeTableRecords($discount->coupons)
        ->assertSee(['ALPHA', 'BRAVO', 'a@example.com'])
        ->assertDontSee('OTHER')
        ->assertSee(__('lunar-filament::discount.relationmanagers.coupons.table.limit.unlimited'));
});

it('searches coupons by code and email', function () {
    $discount = Discount::factory()->coupon('ALPHA', ['email' => 'a@example.com'])->create();
    $bravo = $discount->coupons()->create(['code' => 'BRAVO', 'email' => 'b@example.com']);

    couponManager($discount)
        ->searchTable('BRAVO')
        ->assertCanSeeTableRecords([$bravo])
        ->assertCanNotSeeTableRecords($discount->coupons->where('code', 'ALPHA'))
        ->searchTable('a@example')
        ->assertCanNotSeeTableRecords([$bravo]);
});

it('shows when a coupon was last redeemed', function () {
    $discount = Discount::factory()->coupon('ALPHA')->create();
    $coupon = $discount->coupons->first();
    DiscountRedemption::factory()->create([
        'discount_id' => $discount->id,
        'coupon_id' => $coupon->id,
        'redeemed_at' => now()->subDay()->startOfSecond(),
    ]);

    couponManager($discount)->assertTableColumnStateSet(
        'last_redeemed_at',
        now()->subDay()->startOfSecond()->toDateTimeString(),
        $coupon
    );
});

it('adds a coupon code uppercased', function () {
    $discount = Discount::factory()->coupon()->create();

    couponManager($discount)
        ->callTableAction(CreateAction::class, data: ['code' => 'spring10', 'max_uses' => 3])
        ->assertHasNoFormErrors();

    expect($discount->coupons()->first())
        ->code->toBe('SPRING10')
        ->max_uses->toBe(3);
});

it('rejects a duplicate coupon code', function () {
    $discount = Discount::factory()->coupon('TAKEN')->create();

    couponManager($discount)
        ->callTableAction(CreateAction::class, data: ['code' => 'taken'])
        ->assertHasFormErrors(['code']);

    expect($discount->coupons()->count())->toBe(1);
});

it('requires a positive limit', function () {
    couponManager(Discount::factory()->coupon()->create())
        ->callTableAction(CreateAction::class, data: ['code' => 'ZERO', 'max_uses' => 0])
        ->assertHasFormErrors(['max_uses']);
});

it('deletes a coupon', function () {
    $discount = Discount::factory()->coupon('GONE')->create();
    $coupon = $discount->coupons->first();

    couponManager($discount)->callTableAction(DeleteAction::class, $coupon);

    expect(Coupon::find($coupon->id))->toBeNull();
});

it('bulk deletes coupons', function () {
    $discount = Discount::factory()->coupon('ONE')->create();
    $discount->coupons()->create(['code' => 'TWO']);

    couponManager($discount)->callTableBulkAction(DeleteBulkAction::class, $discount->coupons);

    expect($discount->coupons()->count())->toBe(0);
});

it('only offers the manager on coupon discounts', function () {
    expect(CouponRelationManager::canViewForRecord(Discount::factory()->coupon()->create(), EditDiscount::class))->toBeTrue()
        ->and(CouponRelationManager::canViewForRecord(Discount::factory()->create(), EditDiscount::class))->toBeFalse();
});
