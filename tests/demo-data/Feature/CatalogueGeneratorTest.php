<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductType;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\States\Product\Published;
use Lunar\DemoData\Generators\CatalogueGenerator;
use Lunar\DemoData\Generators\FoundationGenerator;
use Lunar\DemoData\Support\DemoContext;
use Lunar\Tests\DemoData\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function generateCatalogue(int $products = 4): DemoContext
{
    Storage::fake((string) config('lunar.demo-data.asset_disk', 'public'));
    Config::set('lunar.demo-data.scales.small.products', $products);

    $context = DemoContext::fromConfig('small');

    app(FoundationGenerator::class)->generate($context);
    app(CatalogueGenerator::class)->generate($context);

    return $context;
}

test('it builds a product type, collection group and collections', function () {
    generateCatalogue(products: 12);

    expect(ProductType::whereName('General')->exists())->toBeTrue();
    expect(CollectionGroup::whereHandle('shop')->exists())->toBeTrue();
    // The full fixture spans Apparel, Accessories and Home & Living.
    expect(CollectionGroup::whereHandle('shop')->first()->collections()->count())->toBe(3);
});

test('it creates published products with a variant, prices and media', function () {
    generateCatalogue();

    expect(Product::count())->toBe(4);

    $product = Product::query()->whereHas('variants', fn ($q) => $q->where('sku', 'APP-JMP-001'))->first();

    expect($product->status)->toBeInstanceOf(Published::class);
    expect($product->collections()->count())->toBe(1);
    expect($product->getMedia('images'))->toHaveCount(1);

    $variant = $product->variants()->first();
    $gbp = $variant->prices()->whereHas('currency', fn ($q) => $q->where('code', 'GBP'))->first();

    expect($variant->prices()->count())->toBe(3);
    expect($gbp->price)->toBe(8900);
    expect($gbp->list_price)->toBe(12000);
});

test('it varies opening stock so out-of-stock is visible', function () {
    generateCatalogue();

    // Fixture index 0 (the Merino Jumper) is seeded with zero opening stock.
    $variant = Product::query()
        ->whereHas('variants', fn ($q) => $q->where('sku', 'APP-JMP-001'))
        ->first()
        ->variants()
        ->first();

    expect($variant->stock_on_hand)->toBe(0);
    expect($variant->stock_available)->toBe(0);
});

test('it is idempotent', function () {
    $context = generateCatalogue();
    app(CatalogueGenerator::class)->generate($context);

    expect(Product::count())->toBe(4);
    expect(CollectionGroup::whereHandle('shop')->count())->toBe(1);
});

test('it cycles the fixture copy to reach larger product counts', function () {
    generateCatalogue(products: 14);

    expect(Product::count())->toBe(14);
    // The 13th product reuses the first fixture with a numeric suffix.
    expect(ProductVariant::whereSku('APP-JMP-001-2')->exists())->toBeTrue();
});
