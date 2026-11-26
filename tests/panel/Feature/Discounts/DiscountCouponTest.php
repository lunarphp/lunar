<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Coupon;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\DiscountRedemption;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $this->discount = Discount::factory()->coupon('WINTER', ['max_uses' => 100, 'uses' => 4])->create();
});

it('lists the codes on the edit page with their usage', function () {
    $winter = $this->discount->coupons()->sole();
    DiscountRedemption::factory()->for($this->discount)->create(['coupon_id' => $winter->id, 'redeemed_at' => '2026-03-01 10:00:00']);

    $this->get(route('panel.discounts.edit', $this->discount))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('discount.redemption', 'coupon')
            ->has('coupons.data', 1)
            ->where('coupons.data.0.code', 'WINTER')
            ->where('coupons.data.0.uses', 4)
            ->where('coupons.data.0.max_uses', 100)
            ->where('coupons.data.0.last_redeemed_at', fn ($value) => str_starts_with((string) $value, '2026-03-01'))
            ->where('coupons.data.0._actions.delete', route('panel.discounts.coupons.destroy', [$this->discount, $winter]))
            ->where('urls.couponStore', route('panel.discounts.coupons.store', $this->discount))
        );
});

it('searches codes by code and email', function () {
    Coupon::factory()->for($this->discount)->create(['code' => 'SPRING', 'email' => 'jo@example.com']);
    Coupon::factory()->for(Discount::factory()->coupon())->create(['code' => 'WINTER-ELSEWHERE']);

    foreach (['wint' => 'WINTER', 'jo@example' => 'SPRING'] as $term => $code) {
        $this->get(route('panel.discounts.edit', [$this->discount, 'coupons_q' => $term]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('coupons.data', 1)
                ->where('coupons.data.0.code', $code)
                ->where('couponSearch', $term)
            );
    }
});

it('adds a code, uppercased, with an optional limit', function () {
    $this->post(route('panel.discounts.coupons.store', $this->discount), ['code' => ' spring10 ', 'max_uses' => 5])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->post(route('panel.discounts.coupons.store', $this->discount), ['code' => 'summer'])
        ->assertRedirect();

    expect($this->discount->coupons()->pluck('max_uses', 'code')->all())->toEqual([
        'WINTER' => 100,
        'SPRING10' => 5,
        'SUMMER' => null,
    ]);
});

it('rejects a code another coupon already has, whatever its case', function () {
    Coupon::factory()->for(Discount::factory()->coupon())->create(['code' => 'TAKEN']);

    $this->post(route('panel.discounts.coupons.store', $this->discount), ['code' => 'taken'])
        ->assertSessionHasErrors('code');

    $this->post(route('panel.discounts.coupons.store', $this->discount), ['code' => 'FRESH', 'max_uses' => 0])
        ->assertSessionHasErrors('max_uses');

    expect($this->discount->coupons()->count())->toBe(1);
});

it('deletes a code', function () {
    $winter = $this->discount->coupons()->sole();

    $this->delete(route('panel.discounts.coupons.destroy', [$this->discount, $winter]))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($this->discount->coupons()->count())->toBe(0);
});

it('will not delete another discount\'s code through this discount', function () {
    $other = Coupon::factory()->for(Discount::factory()->coupon())->create();

    $this->delete(route('panel.discounts.coupons.destroy', [$this->discount, $other]))->assertNotFound();

    expect($other->fresh())->not->toBeNull();
});

it('keeps coupon management behind the discounts permission', function () {
    $this->actingAs(Staff::factory()->create(['admin' => false]), 'staff');

    $this->post(route('panel.discounts.coupons.store', $this->discount), ['code' => 'NOPE'])->assertForbidden();
    $this->delete(route('panel.discounts.coupons.destroy', [$this->discount, $this->discount->coupons()->sole()]))->assertForbidden();
});
