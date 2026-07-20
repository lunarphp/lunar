<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Brands\CreateBrand;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Collection;
use Lunar\Core\States\Brand\Active;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('creates a brand with the given attributes', function () {
    $brand = app(CreateBrand::class)->execute([
        'name' => 'Stark Industries',
        'handle' => 'stark',
        'status' => 'draft',
    ]);

    expect($brand)->toBeInstanceOf(Brand::class);

    $this->assertDatabaseHas('lunar_brands', [
        'id' => $brand->id,
        'name' => 'Stark Industries',
        'handle' => 'stark',
        'status' => 'draft',
    ]);
});

test('defaults to the active status', function () {
    $brand = app(CreateBrand::class)->execute([
        'name' => 'Stark Industries',
    ]);

    expect($brand->status)->toBeInstanceOf(Active::class);
});

test('generates a handle from the name when none is given', function () {
    $brand = app(CreateBrand::class)->execute([
        'name' => 'Stark Industries',
    ]);

    expect($brand->handle)->toBe('stark-industries');
});

test('suffixes a generated handle until unique', function () {
    Brand::factory()->create(['handle' => 'stark-industries']);
    Brand::factory()->create(['handle' => 'stark-industries-2']);

    $brand = app(CreateBrand::class)->execute([
        'name' => 'Stark Industries',
    ]);

    expect($brand->handle)->toBe('stark-industries-3');
});

test('syncs the given collections', function () {
    $collections = Collection::factory(2)->create();

    $brand = app(CreateBrand::class)->execute([
        'name' => 'Stark Industries',
    ], $collections->pluck('id')->all());

    expect($brand->collections()->get())->toHaveCount(2);
});

test('does not touch collections when none are given', function () {
    $brand = app(CreateBrand::class)->execute([
        'name' => 'Stark Industries',
    ]);

    expect($brand->collections()->get())->toHaveCount(0);
});
