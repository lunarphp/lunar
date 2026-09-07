<?php

use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lunar\Core\Cache\DependencyResolver;
use Lunar\Core\Contracts\CacheDependencies as CacheDependenciesContract;
use Lunar\Core\Facades\CacheDependencies;
use Lunar\Core\Facades\CacheTags;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductAssociation;
use Lunar\Core\Models\ProductOption;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true, 'decimal_places' => 2]);
    Config::set('scout.driver', 'null');
});

function productWithDependencies(): array
{
    $brand = Brand::factory()->create();
    $product = Product::factory()->create(['brand_id' => $brand->id]);

    $collectionA = Collection::factory()->create();
    $collectionB = Collection::factory()->create();
    $product->collections()->attach([$collectionA->id, $collectionB->id]);

    $option = ProductOption::factory()->create();
    $product->productOptions()->attach($option->id, ['position' => 1]);

    $target = Product::factory()->create();
    ProductAssociation::create([
        'product_parent_id' => $product->id,
        'product_target_id' => $target->id,
        'type' => 'cross-sell',
    ]);

    return compact('product', 'brand', 'collectionA', 'collectionB', 'option', 'target');
}

test('the default product graph resolves the full dependency tag set', function () {
    ['product' => $product, 'brand' => $brand, 'collectionA' => $a, 'collectionB' => $b, 'option' => $option, 'target' => $target] = productWithDependencies();

    expect(CacheTags::for($product))->toEqualCanonicalizing([
        "product:{$product->public_id}",
        "brand:{$brand->public_id}",
        "collection:{$a->public_id}",
        "collection:{$b->public_id}",
        "product_option:{$option->public_id}",
        "product:{$target->public_id}",
    ]);
});

test('a dotted path collects the leaf tag and leaves the non-cacheable hop untagged', function () {
    ['product' => $product, 'target' => $target] = productWithDependencies();

    CacheDependencies::define('assoc-only', ['associations.target']);

    // The ProductAssociation hop has no tag; only the target product is collected.
    expect(CacheTags::for($product, 'assoc-only'))->toEqualCanonicalizing([
        "product:{$product->public_id}",
        "product:{$target->public_id}",
    ]);
});

test('a registered graph can be overridden', function () {
    ['product' => $product, 'brand' => $brand] = productWithDependencies();

    CacheDependencies::define('product', ['brand']);

    expect(CacheTags::for($product))->toEqualCanonicalizing([
        "product:{$product->public_id}",
        "brand:{$brand->public_id}",
    ]);
});

test('a closure graph resolves models and tags', function () {
    ['product' => $product, 'brand' => $brand] = productWithDependencies();

    CacheDependencies::define('product-card', fn (Product $p) => [$p->brand, 'static:promo']);

    expect(CacheTags::for($product, 'product-card'))->toEqualCanonicalizing([
        "product:{$product->public_id}",
        "brand:{$brand->public_id}",
        'static:promo',
    ]);
});

test('an unregistered graph resolves to the root tag only', function () {
    ['product' => $product] = productWithDependencies();

    expect(CacheTags::for($product, 'does-not-exist'))->toBe(["product:{$product->public_id}"]);
});

test('tags are deduplicated', function () {
    ['product' => $product, 'brand' => $brand] = productWithDependencies();

    CacheDependencies::define('dupes', ['brand', 'brand']);

    expect(CacheTags::for($product, 'dupes'))->toEqualCanonicalizing([
        "product:{$product->public_id}",
        "brand:{$brand->public_id}",
    ]);
});

test('an unknown relation path throws outside production', function () {
    ['product' => $product] = productWithDependencies();

    CacheDependencies::define('broken', ['nonexistent']);

    CacheTags::for($product, 'broken');
})->throws(RelationNotFoundException::class);

test('an unknown relation path is skipped in production', function () {
    ['product' => $product] = productWithDependencies();

    app(CacheDependenciesContract::class)->define('broken', ['nonexistent']);

    $lenient = new DependencyResolver(app(CacheDependenciesContract::class), strict: false);

    expect($lenient->for($product, 'broken'))->toBe(["product:{$product->public_id}"]);
});
