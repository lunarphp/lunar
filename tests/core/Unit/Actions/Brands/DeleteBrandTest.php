<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Brands\DeleteBrand;
use Lunar\Core\Exceptions\BrandActionException;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Product;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('deletes a brand without products', function () {
    $brand = Brand::factory()->create();

    app(DeleteBrand::class)->execute($brand);

    $this->assertDatabaseMissing('lunar_brands', ['id' => $brand->id]);
});

test('refuses to delete a brand with products', function () {
    $brand = Brand::factory()->create();
    Product::factory()->create(['brand_id' => $brand->id]);

    expect(DeleteBrand::isProtected($brand))->toBeTrue();

    expect(fn () => app(DeleteBrand::class)->execute($brand))
        ->toThrow(BrandActionException::class);

    $this->assertDatabaseHas('lunar_brands', ['id' => $brand->id]);
});
