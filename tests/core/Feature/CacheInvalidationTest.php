<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Lunar\Core\Enums\CacheInvalidationReason;
use Lunar\Core\Events\Catalog\BrandInvalidated;
use Lunar\Core\Events\Catalog\CollectionInvalidated;
use Lunar\Core\Events\Catalog\ProductInvalidated;
use Lunar\Core\Events\Catalog\ProductOptionInvalidated;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductAssociation;
use Lunar\Core\Models\ProductOption;
use Lunar\Core\Models\ProductOptionValue;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\TestCase;

// DatabaseMigrations (not RefreshDatabase): the recorder flushes on transaction
// commit, and RefreshDatabase's wrapping transaction never commits — so its
// after-commit callbacks would never run. Migrating fresh per test gives the
// same transaction semantics as production.
uses(TestCase::class);
uses(DatabaseMigrations::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true, 'decimal_places' => 2]);
});

test('creating a cacheable entity dispatches its invalidation event', function () {
    Event::fake([ProductInvalidated::class]);

    $product = Product::factory()->create();

    Event::assertDispatched(
        ProductInvalidated::class,
        fn (ProductInvalidated $e) => $e->product->is($product) && $e->reason === CacheInvalidationReason::Created,
    );
});

test('updating a cacheable entity dispatches an updated invalidation', function () {
    $product = Product::factory()->create()->fresh();

    Event::fake([ProductInvalidated::class]);

    $product->touch();

    Event::assertDispatched(
        ProductInvalidated::class,
        fn (ProductInvalidated $e) => $e->reason === CacheInvalidationReason::Updated,
    );
});

test('deleting a cacheable entity dispatches a deleted invalidation', function () {
    $product = Product::factory()->create();

    Event::fake([ProductInvalidated::class]);

    $product->delete();

    Event::assertDispatched(
        ProductInvalidated::class,
        fn (ProductInvalidated $e) => $e->reason === CacheInvalidationReason::Deleted,
    );
});

test('collections, brands and options each dispatch their own event', function () {
    Event::fake([CollectionInvalidated::class, BrandInvalidated::class, ProductOptionInvalidated::class]);

    Collection::factory()->create();
    Brand::factory()->create();
    ProductOption::factory()->create();

    Event::assertDispatched(CollectionInvalidated::class);
    Event::assertDispatched(BrandInvalidated::class);
    Event::assertDispatched(ProductOptionInvalidated::class);
});

test('a variant change invalidates its product', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

    Event::fake([ProductInvalidated::class]);

    $variant->touch();

    Event::assertDispatched(
        ProductInvalidated::class,
        fn (ProductInvalidated $e) => $e->product->is($product) && $e->reason === CacheInvalidationReason::RelatedChanged,
    );
});

test('a price change invalidates the variant product', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

    Event::fake([ProductInvalidated::class]);

    Price::factory()->create([
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
        'currency_id' => Currency::first()->id,
    ]);

    Event::assertDispatched(
        ProductInvalidated::class,
        fn (ProductInvalidated $e) => $e->product->is($product),
    );
});

test('adding an associated product invalidates both products', function () {
    $parent = Product::factory()->create();
    $target = Product::factory()->create();

    Event::fake([ProductInvalidated::class]);

    ProductAssociation::create([
        'product_parent_id' => $parent->id,
        'product_target_id' => $target->id,
        'type' => 'cross-sell',
    ]);

    Event::assertDispatched(ProductInvalidated::class, fn (ProductInvalidated $e) => $e->product->is($parent));
    Event::assertDispatched(ProductInvalidated::class, fn (ProductInvalidated $e) => $e->product->is($target));
});

test('an option value change invalidates its option', function () {
    $option = ProductOption::factory()->create();

    Event::fake([ProductOptionInvalidated::class]);

    ProductOptionValue::factory()->create(['product_option_id' => $option->id]);

    Event::assertDispatched(
        ProductOptionInvalidated::class,
        fn (ProductOptionInvalidated $e) => $e->productOption->is($option),
    );
});

test('one entity is invalidated once per transaction however many parts change', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

    Event::fake([ProductInvalidated::class]);

    DB::transaction(function () use ($product, $variant) {
        $product->touch();
        $variant->touch();
    });

    Event::assertDispatchedTimes(ProductInvalidated::class, 1);
});

test('a rolled back change invalidates nothing', function () {
    $product = Product::factory()->create();

    Event::fake([ProductInvalidated::class]);

    rescue(function () use ($product) {
        DB::transaction(function () use ($product) {
            $product->touch();

            throw new RuntimeException('roll back');
        });
    }, report: false);

    Event::assertNotDispatched(ProductInvalidated::class);
});

test('invalidation is held until the transaction commits', function () {
    $product = Product::factory()->create();

    Event::fake([ProductInvalidated::class]);

    DB::transaction(function () use ($product) {
        $product->touch();

        Event::assertNotDispatched(ProductInvalidated::class);
    });

    Event::assertDispatched(ProductInvalidated::class);
});

test('cache tags use the morph alias and key', function () {
    $product = Product::factory()->create();
    $option = ProductOption::factory()->create();

    expect($product->cacheTags())->toBe(["product:{$product->id}"]);
    expect($option->cacheTags())->toBe(["product_option:{$option->id}"]);
});
