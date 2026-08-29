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

it('renders the create screen with the registered types', function () {
    $this->get(route('panel.discounts.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('discounts/Create')
            ->has('types', 3)
            ->has('types.0', fn (Assert $type) => $type->hasAll(['class', 'label', 'component', 'buckets']))
            ->has('urls.store')
        );
});

it('creates a discount and lands on its edit page', function () {
    $response = $this->post(route('panel.discounts.store'), [
        'name' => 'Winter Sale',
        'handle' => 'winter-sale',
        'type' => PercentageOff::class,
        'starts_at' => now()->toDateTimeString(),
    ]);

    $discount = Discount::whereHandle('winter-sale')->firstOrFail();

    $response->assertRedirect(route('panel.discounts.edit', $discount));

    expect($discount->name)->toBe('Winter Sale');
    expect($discount->type)->toBe(PercentageOff::class);
});

it('rejects a duplicate handle', function () {
    Discount::factory()->create(['handle' => 'winter-sale']);

    $this->post(route('panel.discounts.store'), [
        'name' => 'Winter Sale',
        'handle' => 'winter-sale',
        'type' => PercentageOff::class,
        'starts_at' => now()->toDateTimeString(),
    ])->assertSessionHasErrors('handle');
});

it('rejects a type that is not registered', function () {
    $this->post(route('panel.discounts.store'), [
        'name' => 'Winter Sale',
        'handle' => 'winter-sale',
        'type' => 'Acme\\Discounts\\NotRegistered',
        'starts_at' => now()->toDateTimeString(),
    ])->assertSessionHasErrors('type');
});

it('is gated on the manage-discounts permission', function () {
    auth('staff')->logout();

    $this->actingAs(Staff::factory()->create(['admin' => false]), 'staff')
        ->get(route('panel.discounts.create'))
        ->assertForbidden();
});
