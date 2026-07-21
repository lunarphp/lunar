<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\ProductType;
use Lunar\Core\States\ProductType\Active;
use Lunar\Core\States\ProductType\Draft;
use Lunar\Tests\Core\TestCase;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('Active → Draft', function () {
    $productType = ProductType::factory()->create(['status' => Active::$name]);
    $productType->status->transitionTo(Draft::class);
    expect($productType->fresh()->status)->toBeInstanceOf(Draft::class);
});

test('Draft → Active', function () {
    $productType = ProductType::factory()->create(['status' => Draft::$name]);
    $productType->status->transitionTo(Active::class);
    expect($productType->fresh()->status)->toBeInstanceOf(Active::class);
});

test('transitioning to the same state is not allowed', function () {
    $productType = ProductType::factory()->create(['status' => Active::$name]);

    expect(fn () => $productType->status->transitionTo(Active::class))
        ->toThrow(CouldNotPerformTransition::class);
});
