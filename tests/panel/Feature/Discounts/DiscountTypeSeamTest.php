<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\Fixtures\DiscountFixtureTestCase;
use Lunar\Tests\Panel\Fixtures\Discounts\FixtureDiscountType;

uses(DiscountFixtureTestCase::class);

beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');
});

it('offers an out-of-core type in the picker', function () {
    $this->get(route('panel.discounts.create'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('types', fn ($types) => collect($types)->contains(
                fn ($type) => $type['class'] === FixtureDiscountType::class
                    && $type['label'] === 'Fixture discount'
                    && $type['component'] === 'fixture::DiscountForm'
                    && $type['buckets'] === ['limitation']
            ))
        );
});

it('renders the type its own component and declared buckets', function () {
    $discount = Discount::factory()->create([
        'type' => FixtureDiscountType::class,
        'data' => ['tier' => 250],
    ]);

    $this->get(route('panel.discounts.edit', $discount))
        ->assertInertia(fn (Assert $page) => $page
            ->where('type.component', 'fixture::DiscountForm')
            ->where('type.buckets', ['limitation'])
            ->where('typeRegistered', true)
            // toForm() scaled the stored minor units down for editing.
            ->where('discount.data.tier', 2.5)
        );
});

it('runs the type payload back through toStorage on update', function () {
    $discount = Discount::factory()->create([
        'type' => FixtureDiscountType::class,
        'data' => ['tier' => 250],
    ]);

    $this->put(route('panel.discounts.update', $discount), [
        'name' => $discount->name,
        'handle' => $discount->handle,
        'type' => FixtureDiscountType::class,
        'starts_at' => $discount->starts_at->toDateTimeString(),
        'data' => ['tier' => 7.5],
    ])->assertRedirect();

    expect($discount->refresh()->data)->toBe(['tier' => 750]);
});

it('applies the type own validation rules to the data payload', function () {
    $discount = Discount::factory()->create(['type' => FixtureDiscountType::class]);

    $this->put(route('panel.discounts.update', $discount), [
        'name' => $discount->name,
        'handle' => $discount->handle,
        'type' => FixtureDiscountType::class,
        'starts_at' => $discount->starts_at->toDateTimeString(),
        'data' => ['tier' => 'not a number'],
    ])->assertSessionHasErrors('data.tier');
});
