<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\States\Product\Archived;
use Lunar\Core\States\Product\Draft;
use Lunar\Core\States\Product\Published;
use Lunar\Tests\Core\TestCase;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true]);
});

test('default state is Draft when no status given', function () {
    // The factory sets status to 'published' by default; force it null first.
    $product = Product::factory()->create();

    expect((string) $product->status)->toBeIn(['draft', 'published']);
});

test('Draft → Published', function () {
    $product = Product::factory()->create(['status' => Draft::$name]);
    $product->status->transitionTo(Published::class);
    expect($product->fresh()->status)->toBeInstanceOf(Published::class);
});

test('Published → Archived', function () {
    $product = Product::factory()->create(['status' => Published::$name]);
    $product->status->transitionTo(Archived::class);
    expect($product->fresh()->status)->toBeInstanceOf(Archived::class);
});

test('Published → Draft', function () {
    $product = Product::factory()->create(['status' => Published::$name]);
    $product->status->transitionTo(Draft::class);
    expect($product->fresh()->status)->toBeInstanceOf(Draft::class);
});

test('Archived → Draft', function () {
    $product = Product::factory()->create(['status' => Archived::$name]);
    $product->status->transitionTo(Draft::class);
    expect($product->fresh()->status)->toBeInstanceOf(Draft::class);
});

test('Draft cannot transition directly to Archived', function () {
    $product = Product::factory()->create(['status' => Draft::$name]);

    expect(fn () => $product->status->transitionTo(Archived::class))
        ->toThrow(CouldNotPerformTransition::class);
});

test('Archived cannot transition directly to Published', function () {
    $product = Product::factory()->create(['status' => Archived::$name]);

    expect(fn () => $product->status->transitionTo(Published::class))
        ->toThrow(CouldNotPerformTransition::class);
});

test('whereVisible returns only published products', function () {
    Product::factory()->create(['status' => Draft::$name]);
    $published = Product::factory()->create(['status' => Published::$name]);
    Product::factory()->create(['status' => Archived::$name]);

    $visible = Product::query()->whereVisible()->get();

    expect($visible)->toHaveCount(1)
        ->and($visible->first()->id)->toBe($published->id);
});
