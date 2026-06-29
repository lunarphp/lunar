<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Lunar\Core\Models\Collection;
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

    $group = CollectionGroup::whereHandle('shop')->first();

    // Three top-level categories: Apparel, Accessories and Home & Living.
    expect($group->collections()->whereNull('parent_id')->count())->toBe(3);
    // Each with nested sub-collections.
    expect($group->collections()->whereNotNull('parent_id')->count())->toBeGreaterThan(0);
});

test('it nests sub-collections under their category', function () {
    generateCatalogue(products: 12);

    $apparel = Collection::query()->whereNull('parent_id')->where('name->en', 'Apparel')->first();

    // Knitwear, T-Shirts, Denim and Outerwear.
    expect($apparel->children()->count())->toBe(4);

    $child = $apparel->children()->first();
    expect($child->parent_id)->toBe($apparel->id);
    expect($child->parent->is($apparel))->toBeTrue();

    // Products are shelved on the leaf sub-collection, not the category.
    $product = Product::query()->whereHas('variants', fn ($q) => $q->where('sku', 'APP-JMP-001'))->first();
    expect($product->collections()->first()->parent_id)->not->toBeNull();
});

test('it creates published products with a variant, prices and media', function () {
    generateCatalogue();

    expect(Product::count())->toBe(4);

    $product = Product::query()->whereHas('variants', fn ($q) => $q->where('sku', 'APP-JMP-001'))->first();

    expect($product->status)->toBeInstanceOf(Published::class);
    expect($product->collections()->count())->toBe(1);
    expect($product->getMedia('images'))->toHaveCount(1);

    $variant = $product->variants()->first();
    // The fixture price is the default-currency (USD) amount; £/€ derive from it.
    $usd = $variant->prices()->whereHas('currency', fn ($q) => $q->where('code', 'USD'))->first();

    expect($variant->prices()->count())->toBe(3);
    expect($usd->price)->toBe(8900);
    expect($usd->list_price)->toBe(12000);
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
