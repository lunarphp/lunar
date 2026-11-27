<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\DiscountTypes\BuyXGetY;
use Lunar\Core\DiscountTypes\FixedAmountOff;
use Lunar\Core\DiscountTypes\PercentageOff;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    // Creating catalogue records runs the URL generator, which needs a default.
    Language::factory()->create(['default' => true]);

    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');
});

it('marks the buckets a type does nothing without', function () {
    // BuyXGetY matches no cart line with an empty condition or reward bucket,
    // while an empty limitation bucket on the line-targeting types means
    // "every line".
    $expected = [
        PercentageOff::class => [],
        FixedAmountOff::class => [],
        BuyXGetY::class => ['condition', 'reward'],
    ];

    foreach ($expected as $type => $required) {
        $discount = Discount::factory()->create(['type' => $type]);

        $this->get(route('panel.discounts.edit', $discount))
            ->assertInertia(fn (Assert $page) => $page->where('type.required_buckets', $required));
    }
});

it('flags list rows whose required buckets are empty', function () {
    $empty = Discount::factory()->create(['type' => BuyXGetY::class, 'priority' => 1]);

    $halfDone = Discount::factory()->create(['type' => BuyXGetY::class, 'priority' => 2]);
    $halfDone->discountables()->create([
        'discountable_type' => Product::morphName(),
        'discountable_id' => Product::factory()->create()->id,
        'type' => 'condition',
    ]);

    $complete = Discount::factory()->create(['type' => BuyXGetY::class, 'priority' => 3]);
    $complete->discountables()->create([
        'discountable_type' => Product::morphName(),
        'discountable_id' => Product::factory()->create()->id,
        'type' => 'condition',
    ]);
    $complete->discountables()->create([
        'discountable_type' => Collection::morphName(),
        'discountable_id' => Collection::factory()->create()->id,
        'type' => 'reward',
    ]);

    // An untargeted percentage discount applies to every line, so it is fine.
    $percentage = Discount::factory()->create(['type' => PercentageOff::class, 'priority' => 4]);

    $this->get(route('panel.discounts.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('discounts.data.0.id', $empty->id)
            ->where('discounts.data.0.missing_targets', ['condition', 'reward'])
            ->where('discounts.data.1.id', $halfDone->id)
            ->where('discounts.data.1.missing_targets', ['reward'])
            ->where('discounts.data.2.id', $complete->id)
            ->where('discounts.data.2.missing_targets', [])
            ->where('discounts.data.3.id', $percentage->id)
            ->where('discounts.data.3.missing_targets', [])
        );
});
