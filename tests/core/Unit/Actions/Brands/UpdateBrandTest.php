<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Brands\UpdateBrand;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Collection;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('updates the brand attributes', function () {
    $brand = Brand::factory()->create(['name' => 'Old Name']);

    app(UpdateBrand::class)->execute($brand, [
        'name' => 'New Name',
        'status' => 'draft',
    ]);

    $this->assertDatabaseHas('lunar_brands', [
        'id' => $brand->id,
        'name' => 'New Name',
        'status' => 'draft',
    ]);
});

test('syncs the given collections', function () {
    $brand = Brand::factory()->create();
    $original = Collection::factory()->create();
    $brand->collections()->attach($original);

    $replacements = Collection::factory(2)->create();

    app(UpdateBrand::class)->execute($brand, [], $replacements->pluck('id')->all());

    expect($brand->collections()->pluck('collection_id')->sort()->values()->all())
        ->toBe($replacements->pluck('id')->sort()->values()->all());
});

test('null leaves collections untouched and an empty array clears them', function () {
    $brand = Brand::factory()->create();
    $brand->collections()->attach(Collection::factory()->create());

    app(UpdateBrand::class)->execute($brand, ['name' => 'Renamed']);
    expect($brand->collections()->get())->toHaveCount(1);

    app(UpdateBrand::class)->execute($brand, [], []);
    expect($brand->collections()->get())->toHaveCount(0);
});
