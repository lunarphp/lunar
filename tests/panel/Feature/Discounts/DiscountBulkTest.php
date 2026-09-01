<?php

use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');
});

it('ends a selection now without deleting it', function () {
    $ended = Discount::factory()->create(['starts_at' => now()->subWeek(), 'ends_at' => now()->addWeek()]);
    $untouched = Discount::factory()->create(['starts_at' => now()->subWeek(), 'ends_at' => now()->addWeek()]);

    $this->post(route('panel.discounts.bulk-end'), ['ids' => [$ended->id]])->assertRedirect();

    expect($ended->refresh()->ends_at->timestamp)->toBe(now()->timestamp);
    expect($ended->status)->toBe(Discount::EXPIRED);
    expect($untouched->refresh()->status)->toBe(Discount::ACTIVE);
});

it('deletes a selection', function () {
    $deleted = Discount::factory()->count(2)->create();
    $kept = Discount::factory()->create();

    $this->post(route('panel.discounts.bulk-destroy'), ['ids' => $deleted->pluck('id')->all()])
        ->assertRedirect();

    expect(Discount::count())->toBe(1);
    expect(Discount::first()->id)->toBe($kept->id);
});

it('validates the selection', function () {
    $this->post(route('panel.discounts.bulk-end'), [])->assertSessionHasErrors('ids');
    $this->post(route('panel.discounts.bulk-destroy'), ['ids' => [93756]])->assertSessionHasErrors('ids.0');
});

it('is gated on the manage-discounts permission', function () {
    auth('staff')->logout();

    $discount = Discount::factory()->create();

    $this->actingAs(Staff::factory()->create(['admin' => false]), 'staff');

    $this->post(route('panel.discounts.bulk-end'), ['ids' => [$discount->id]])->assertForbidden();
    $this->post(route('panel.discounts.bulk-destroy'), ['ids' => [$discount->id]])->assertForbidden();
});
