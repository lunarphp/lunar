<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Promotion;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('a promotion groups discounts', function () {
    $promotion = Promotion::factory()->create();

    $discount = Discount::factory()->create(['promotion_id' => $promotion->id]);
    Discount::factory()->create(['promotion_id' => null]);

    expect($promotion->discounts)->toHaveCount(1);
    expect($promotion->discounts->first()->id)->toBe($discount->id);
    expect($discount->promotion->id)->toBe($promotion->id);
});

test('a promotion mints a public_id and translates its name', function () {
    $promotion = Promotion::factory()->create([
        'public_id' => null,
        'name' => ['en' => 'World Cup 2026'],
    ]);

    expect(Str::isUlid($promotion->public_id))->toBeTrue();
    expect($promotion->translate('name'))->toBe('World Cup 2026');
});

test('the active scope matches open or unbounded windows', function () {
    $unbounded = Promotion::factory()->create(['starts_at' => null, 'ends_at' => null]);
    $open = Promotion::factory()->create(['starts_at' => now()->subDay(), 'ends_at' => now()->addDay()]);
    $future = Promotion::factory()->create(['starts_at' => now()->addDay(), 'ends_at' => null]);
    $past = Promotion::factory()->create(['starts_at' => now()->subWeek(), 'ends_at' => now()->subDay()]);

    $active = Promotion::active()->pluck('id');

    expect($active)
        ->toContain($unbounded->id)
        ->toContain($open->id)
        ->not->toContain($future->id)
        ->not->toContain($past->id);
});
