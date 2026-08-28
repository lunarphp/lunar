<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\DiscountTypes\BuyXGetY;
use Lunar\Core\DiscountTypes\FixedAmountOff;
use Lunar\Core\DiscountTypes\PercentageOff;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Currency::factory()->create(['code' => 'GBP', 'decimal_places' => 2, 'default' => true, 'enabled' => true]);
    Currency::factory()->create(['code' => 'JPY', 'decimal_places' => 0, 'default' => false, 'enabled' => true]);

    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');
});

it('gives each first-party type its own component and buckets', function () {
    $expected = [
        PercentageOff::class => ['PercentageOffForm', ['limitation', 'exclusion']],
        FixedAmountOff::class => ['FixedAmountOffForm', ['limitation', 'exclusion']],
        BuyXGetY::class => ['BuyXGetYForm', ['condition', 'reward']],
    ];

    foreach ($expected as $type => [$component, $buckets]) {
        $discount = Discount::factory()->create(['type' => $type]);

        $this->get(route('panel.discounts.edit', $discount))
            ->assertInertia(fn (Assert $page) => $page
                ->where('type.component', $component)
                ->where('type.buckets', $buckets)
            );
    }
});

it('shows the decimal amount on the edit screen and stores minor units', function () {
    $discount = Discount::factory()->create([
        'type' => FixedAmountOff::class,
        'data' => ['amounts' => ['GBP' => 1250, 'JPY' => 400]],
    ]);

    $this->get(route('panel.discounts.edit', $discount))
        ->assertInertia(fn (Assert $page) => $page
            ->where('discount.data.amounts.GBP', 12.5)
            // A zero-decimal currency is stored and shown at the same scale.
            ->where('discount.data.amounts.JPY', 400)
        );

    $this->put(route('panel.discounts.update', $discount), [
        'name' => $discount->name,
        'handle' => $discount->handle,
        'type' => FixedAmountOff::class,
        'starts_at' => $discount->starts_at->toDateTimeString(),
        'data' => ['amounts' => ['GBP' => 9.99, 'JPY' => 750]],
    ])->assertSessionHasNoErrors();

    expect($discount->refresh()->data['amounts'])->toBe(['GBP' => 999, 'JPY' => 750]);
});

it('keeps the minimum spend when the type payload is saved', function () {
    $discount = Discount::factory()->create([
        'type' => PercentageOff::class,
        'data' => ['percentage' => 10, 'min_prices' => ['GBP' => 5000]],
    ]);

    $this->put(route('panel.discounts.update', $discount), [
        'name' => $discount->name,
        'handle' => $discount->handle,
        'type' => PercentageOff::class,
        'starts_at' => $discount->starts_at->toDateTimeString(),
        'data' => ['percentage' => 20, 'min_prices' => ['GBP' => 50]],
    ])->assertSessionHasNoErrors();

    // toEqual, not toBe: a whole percentage comes back from jsonb as an int, and
    // its int/float representation in storage is not a contract.
    expect($discount->refresh()->data)->toEqual([
        'percentage' => 20,
        'min_prices' => ['GBP' => 5000],
    ]);
});

it('validates the type payload against the selected type rules', function () {
    $discount = Discount::factory()->create(['type' => PercentageOff::class]);

    $this->put(route('panel.discounts.update', $discount), [
        'name' => $discount->name,
        'handle' => $discount->handle,
        'type' => PercentageOff::class,
        'starts_at' => $discount->starts_at->toDateTimeString(),
        'data' => ['percentage' => 150],
    ])->assertSessionHasErrors('data.percentage');
});

it('validates the minimum spend for every enabled currency', function () {
    $discount = Discount::factory()->create(['type' => PercentageOff::class]);

    $this->put(route('panel.discounts.update', $discount), [
        'name' => $discount->name,
        'handle' => $discount->handle,
        'type' => PercentageOff::class,
        'starts_at' => $discount->starts_at->toDateTimeString(),
        'data' => ['percentage' => 10, 'min_prices' => ['JPY' => 'lots']],
    ])->assertSessionHasErrors('data.min_prices.JPY');
});

it('summarises the effect in the list', function () {
    Discount::factory()->create([
        'name' => 'Fifteen off',
        'type' => PercentageOff::class,
        'data' => ['percentage' => 15],
    ]);

    $this->get(route('panel.discounts.index', ['q' => 'Fifteen']))
        ->assertInertia(fn (Assert $page) => $page->where('discounts.data.0.effect', '15% off'));
});

it('leaves the effect empty for a type that cannot summarise itself', function () {
    Discount::factory()->create(['name' => 'Unknown type', 'type' => 'Acme\\Discounts\\Removed']);

    $this->get(route('panel.discounts.index', ['q' => 'Unknown']))
        ->assertInertia(fn (Assert $page) => $page->where('discounts.data.0.effect', null));
});
