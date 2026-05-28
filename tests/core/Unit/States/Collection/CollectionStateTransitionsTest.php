<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\States\Collection\Archived;
use Lunar\Core\States\Collection\Draft;
use Lunar\Core\States\Collection\Published;
use Lunar\Tests\Core\TestCase;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true]);
});

test('Draft → Published', function () {
    $collection = Collection::factory()->create(['status' => Draft::$name]);
    $collection->status->transitionTo(Published::class);
    expect($collection->fresh()->status)->toBeInstanceOf(Published::class);
});

test('Published → Archived', function () {
    $collection = Collection::factory()->create(['status' => Published::$name]);
    $collection->status->transitionTo(Archived::class);
    expect($collection->fresh()->status)->toBeInstanceOf(Archived::class);
});

test('Draft → Archived', function () {
    $collection = Collection::factory()->create(['status' => Draft::$name]);
    $collection->status->transitionTo(Archived::class);
    expect($collection->fresh()->status)->toBeInstanceOf(Archived::class);
});

test('transitioning to the same state is not allowed', function () {
    $collection = Collection::factory()->create(['status' => Draft::$name]);

    expect(fn () => $collection->status->transitionTo(Draft::class))
        ->toThrow(CouldNotPerformTransition::class);
});

test('whereVisible returns only published collections', function () {
    $published = Collection::factory()->create(['status' => Published::$name]);
    Collection::factory()->create(['status' => Draft::$name]);
    Collection::factory()->create(['status' => Archived::$name]);

    expect(Collection::query()->whereVisible()->get()->pluck('id'))
        ->toContain($published->id)
        ->and(Collection::query()->whereVisible()->count())->toBe(1);
});
