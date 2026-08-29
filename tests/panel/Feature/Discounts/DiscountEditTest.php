<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\DiscountTypes\PercentageOff;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');
});

it('renders the edit screen with the discount, its type schema and availability', function () {
    $discount = Discount::factory()->create([
        'type' => PercentageOff::class,
        'data' => ['percentage' => 15],
    ]);

    $this->get(route('panel.discounts.edit', $discount))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('discounts/Edit')
            ->where('discount.id', $discount->id)
            ->where('discount.type', PercentageOff::class)
            ->where('typeRegistered', true)
            ->has('type', fn (Assert $type) => $type->hasAll(['class', 'label', 'component', 'buckets']))
            ->has('availability.channels')
            ->has('availability.customer_groups')
            ->has('availabilityValues')
            ->has('currencies')
            ->has('activities')
            ->has('urls.draftCommit')
        );
});

it('falls back to the raw json editor for a type with no registered form', function () {
    // PercentageOff has no panel form yet; the fallback is what keeps any type
    // editable rather than making it disappear.
    $discount = Discount::factory()->create(['type' => PercentageOff::class]);

    $this->get(route('panel.discounts.edit', $discount))
        ->assertInertia(fn (Assert $page) => $page
            ->where('type.component', 'RawDataForm')
            ->where('type.buckets', ['limitation', 'exclusion', 'condition', 'reward'])
        );
});

it('still renders a discount whose type is no longer registered', function () {
    $discount = Discount::factory()->create(['type' => 'Acme\\Discounts\\Removed']);

    $this->get(route('panel.discounts.edit', $discount))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('typeRegistered', false)
            ->where('type.label', 'Acme\\Discounts\\Removed')
            ->where('type.component', 'RawDataForm')
        );
});

it('updates a discount', function () {
    $discount = Discount::factory()->create(['name' => 'Original', 'priority' => 1]);

    $this->put(route('panel.discounts.update', $discount), [
        'name' => 'Renamed',
        'handle' => $discount->handle,
        'type' => $discount->type,
        'starts_at' => $discount->starts_at->toDateTimeString(),
        'priority' => 20,
        'stop' => true,
    ])->assertRedirect();

    $discount->refresh();

    expect($discount->name)->toBe('Renamed');
    expect($discount->priority)->toBe(20);
    expect((bool) $discount->stop)->toBeTrue();
});

it('accepts a handle in the shape the Filament admin produces', function () {
    // Str::snake leaves punctuation alone, so handles like this are already in
    // the wild; the panel must not refuse to save them.
    $discount = Discount::factory()->create(['handle' => "sofia_o'kon"]);

    $this->put(route('panel.discounts.update', $discount), [
        'name' => 'Renamed',
        'handle' => $discount->handle,
        'type' => $discount->type,
        'starts_at' => $discount->starts_at->toDateTimeString(),
    ])->assertSessionHasNoErrors();

    expect($discount->refresh()->name)->toBe('Renamed');
});

it('rejects an end date before the start date', function () {
    $discount = Discount::factory()->create(['starts_at' => now()]);

    $this->put(route('panel.discounts.update', $discount), [
        'name' => $discount->name,
        'handle' => $discount->handle,
        'type' => $discount->type,
        'starts_at' => now()->toDateTimeString(),
        'ends_at' => now()->subDay()->toDateTimeString(),
    ])->assertSessionHasErrors('ends_at');
});

it('deletes a discount and returns to the index', function () {
    $discount = Discount::factory()->create();

    $this->delete(route('panel.discounts.destroy', $discount))
        ->assertRedirect(route('panel.discounts.index'));

    expect(Discount::find($discount->id))->toBeNull();
});

it('is gated on the manage-discounts permission', function () {
    auth('staff')->logout();

    $discount = Discount::factory()->create();
    $staff = Staff::factory()->create(['admin' => false]);

    $this->actingAs($staff, 'staff');

    $this->get(route('panel.discounts.edit', $discount))->assertForbidden();
    $this->put(route('panel.discounts.update', $discount), [])->assertForbidden();
    $this->delete(route('panel.discounts.destroy', $discount))->assertForbidden();
});
