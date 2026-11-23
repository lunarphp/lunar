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

it('ends a scheduled discount with a window the edit form can still save', function () {
    $discount = Discount::factory()->create(['starts_at' => now()->addMonths(6), 'ends_at' => null]);

    $this->post(route('panel.discounts.bulk-end'), ['ids' => [$discount->id]])->assertRedirect();

    $discount->refresh();

    expect($discount->status)->toBe(Discount::EXPIRED);
    expect(Discount::active()->whereKey($discount->id)->exists())->toBeFalse();
    expect($discount->starts_at->lt($discount->ends_at))->toBeTrue();

    // The edit form round-trips both dates through datetime-local inputs, so
    // it submits them at minute precision.
    $this->put(route('panel.discounts.update', $discount), [
        'name' => $discount->name,
        'handle' => $discount->handle,
        'type' => $discount->type,
        'starts_at' => $discount->starts_at->format('Y-m-d\TH:i'),
        'ends_at' => $discount->ends_at->format('Y-m-d\TH:i'),
    ])->assertSessionHasNoErrors();

    expect(Discount::active()->whereKey($discount->id)->exists())->toBeFalse();
});

it('leaves the end date of an already ended discount alone', function () {
    $endedAt = now()->subWeek()->startOfSecond();
    $discount = Discount::factory()->create(['starts_at' => now()->subMonth(), 'ends_at' => $endedAt]);
    $running = Discount::factory()->create(['starts_at' => now()->subMonth(), 'ends_at' => null]);

    $response = $this->post(route('panel.discounts.bulk-end'), ['ids' => [$discount->id, $running->id]]);

    expect($discount->refresh()->ends_at->equalTo($endedAt))->toBeTrue();
    expect($running->refresh()->status)->toBe(Discount::EXPIRED);

    $response->assertSessionHas('success', 'Ended 1 discount.');
});

it('pluralises the bulk flash messages', function () {
    $discounts = Discount::factory()->count(2)->create(['starts_at' => now()->subWeek(), 'ends_at' => null]);

    $this->post(route('panel.discounts.bulk-end'), ['ids' => $discounts->pluck('id')->all()])
        ->assertSessionHas('success', 'Ended 2 discounts.');

    $this->post(route('panel.discounts.bulk-destroy'), ['ids' => [$discounts->first()->id]])
        ->assertSessionHas('success', 'Deleted 1 discount.');
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
