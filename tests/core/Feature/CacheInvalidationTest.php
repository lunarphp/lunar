<?php

use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\NullEngine;
use Lunar\Core\Cache\CacheInvalidator;
use Lunar\Core\Enums\CacheInvalidationReason;
use Lunar\Core\Events\Catalog\BrandInvalidated;
use Lunar\Core\Events\Catalog\CollectionInvalidated;
use Lunar\Core\Events\Catalog\ProductInvalidated;
use Lunar\Core\Events\Catalog\ProductOptionInvalidated;
use Lunar\Core\Listeners\ReindexOnCacheInvalidation;
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

test('removing an associated product invalidates both products', function () {
    $parent = Product::factory()->create();
    $target = Product::factory()->create();
    $association = ProductAssociation::create([
        'product_parent_id' => $parent->id,
        'product_target_id' => $target->id,
        'type' => 'cross-sell',
    ]);

    Event::fake([ProductInvalidated::class]);

    $association->delete();

    Event::assertDispatched(ProductInvalidated::class, fn (ProductInvalidated $e) => $e->product->is($parent));
    Event::assertDispatched(ProductInvalidated::class, fn (ProductInvalidated $e) => $e->product->is($target));
});

test('re-parenting a collection invalidates its descendant subtree', function () {
    $root = Collection::factory()->create();
    $group = ['collection_group_id' => $root->collection_group_id];

    $branch = Collection::factory()->create($group);
    $branch->appendToNode($root)->save();
    $leaf = Collection::factory()->create($group);
    $leaf->appendToNode($branch)->save();

    $newRoot = Collection::factory()->create($group);

    Event::fake([CollectionInvalidated::class]);

    $branch->refresh()->appendToNode($newRoot)->save();

    // The moved node (self) and every descendant are invalidated.
    Event::assertDispatched(CollectionInvalidated::class, fn (CollectionInvalidated $e) => $e->collection->is($branch));
    Event::assertDispatched(CollectionInvalidated::class, fn (CollectionInvalidated $e) => $e->collection->is($leaf));
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

test('attaching a product to a collection invalidates both via the native relation', function () {
    $product = Product::factory()->create();
    $collection = Collection::factory()->create();

    Event::fake([ProductInvalidated::class, CollectionInvalidated::class]);

    $product->collections()->attach($collection->id);

    Event::assertDispatched(ProductInvalidated::class, fn (ProductInvalidated $e) => $e->product->is($product));
    Event::assertDispatched(CollectionInvalidated::class, fn (CollectionInvalidated $e) => $e->collection->is($collection));
});

test('syncing collection products invalidates both sides via the native relation', function () {
    $collection = Collection::factory()->create();
    $product = Product::factory()->create();

    Event::fake([ProductInvalidated::class, CollectionInvalidated::class]);

    $collection->products()->sync([$product->id]);

    Event::assertDispatched(CollectionInvalidated::class, fn (CollectionInvalidated $e) => $e->collection->is($collection));
    Event::assertDispatched(ProductInvalidated::class, fn (ProductInvalidated $e) => $e->product->is($product));
});

test('detaching a collection from a product invalidates the product', function () {
    $product = Product::factory()->create();
    $collection = Collection::factory()->create();
    $product->collections()->attach($collection->id);

    Event::fake([ProductInvalidated::class]);

    $product->collections()->detach($collection->id);

    Event::assertDispatched(ProductInvalidated::class, fn (ProductInvalidated $e) => $e->product->is($product));
});

test('a non-cacheable related side does not break a native pivot write', function () {
    $product = Product::factory()->create();

    Event::fake([ProductInvalidated::class]);

    // Channels are not cacheable entities; only the product is invalidated.
    $product->channels()->detach();

    Event::assertDispatched(ProductInvalidated::class, fn (ProductInvalidated $e) => $e->product->is($product));
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

test('a cascade invalidation reindexes the searchable parent', function () {
    $product = Product::factory()->create();

    // Install the spy after creation so the factory's own Scout sync is excluded.
    $engine = Mockery::spy(NullEngine::class);
    $manager = Mockery::mock(EngineManager::class);
    $manager->shouldReceive('engine')->andReturn($engine);
    app()->instance(EngineManager::class, $manager);

    // RelatedChanged (a variant/price/pivot changed) is the cascade Scout misses.
    (new ReindexOnCacheInvalidation)->handle(new ProductInvalidated($product, CacheInvalidationReason::RelatedChanged));

    $engine->shouldHaveReceived('update');
});

test('a direct change is left to scout and not reindexed by the listener', function () {
    $product = Product::factory()->create();

    $engine = Mockery::spy(NullEngine::class);
    $manager = Mockery::mock(EngineManager::class);
    $manager->shouldReceive('engine')->andReturn($engine);
    app()->instance(EngineManager::class, $manager);

    (new ReindexOnCacheInvalidation)->handle(new ProductInvalidated($product, CacheInvalidationReason::Updated));

    $engine->shouldNotHaveReceived('update');
});

test('a root rollback does not leak into a later autocommit mutation', function () {
    $rolledBack = Product::factory()->create();
    $later = Product::factory()->create();

    Event::fake([ProductInvalidated::class]);

    rescue(function () use ($rolledBack) {
        DB::transaction(function () use ($rolledBack) {
            $rolledBack->touch();

            throw new RuntimeException('roll back');
        });
    }, report: false);

    $later->touch();

    Event::assertDispatchedTimes(ProductInvalidated::class, 1);
    Event::assertDispatched(
        ProductInvalidated::class,
        fn (ProductInvalidated $e) => $e->product->is($later),
    );
});

test('a root rollback does not leak into a later committed transaction', function () {
    $rolledBack = Product::factory()->create();
    $later = Product::factory()->create();

    Event::fake([ProductInvalidated::class]);

    rescue(function () use ($rolledBack) {
        DB::transaction(function () use ($rolledBack) {
            $rolledBack->touch();

            throw new RuntimeException('roll back');
        });
    }, report: false);

    DB::transaction(function () use ($later) {
        $later->touch();
    });

    Event::assertDispatchedTimes(ProductInvalidated::class, 1);
    Event::assertDispatched(
        ProductInvalidated::class,
        fn (ProductInvalidated $e) => $e->product->is($later),
    );
});

test('a later transaction for the same target still invalidates after a rollback', function () {
    $product = Product::factory()->create();

    Event::fake([ProductInvalidated::class]);

    rescue(function () use ($product) {
        DB::transaction(function () use ($product) {
            $product->touch();

            throw new RuntimeException('roll back');
        });
    }, report: false);

    DB::transaction(function () use ($product) {
        $product->touch();
    });

    Event::assertDispatchedTimes(ProductInvalidated::class, 1);
});

test('a nested rollback does not prevent the outer commit from invalidating', function () {
    $outer = Product::factory()->create();
    $nested = Product::factory()->create();

    Event::fake([ProductInvalidated::class]);

    DB::transaction(function () use ($outer, $nested) {
        $outer->touch();

        rescue(function () use ($nested) {
            DB::transaction(function () use ($nested) {
                $nested->touch();

                throw new RuntimeException('roll back nested');
            });
        }, report: false);
    });

    Event::assertDispatched(
        ProductInvalidated::class,
        fn (ProductInvalidated $e) => $e->product->is($outer),
    );
    Event::assertNotDispatched(
        ProductInvalidated::class,
        fn (ProductInvalidated $e) => $e->product->is($nested),
    );
});

test('a target touched in both outer and nested frames survives the nested rollback', function () {
    $product = Product::factory()->create();

    Event::fake([ProductInvalidated::class]);

    DB::transaction(function () use ($product) {
        $product->touch();

        rescue(function () use ($product) {
            DB::transaction(function () use ($product) {
                $product->touch();

                throw new RuntimeException('roll back nested');
            });
        }, report: false);
    });

    Event::assertDispatched(
        ProductInvalidated::class,
        fn (ProductInvalidated $e) => $e->product->is($product),
    );
});

test('scope-fresh recorder instances register no listeners on the shared dispatcher', function () {
    $product = Product::factory()->create();

    $countListeners = fn () => count(Event::getRawListeners()[TransactionRolledBack::class] ?? []);
    $baseline = $countListeners();

    // Simulates successive request/job scopes under the scoped binding: each
    // scope gets a fresh recorder, and rollback pruning must go through the
    // provider's single process-lifetime listener rather than piling
    // instance-bound closures onto the dispatcher for the worker's lifetime.
    foreach (range(1, 3) as $i) {
        $invalidator = new CacheInvalidator(app('db'), config('lunar.database.connection'));

        rescue(function () use ($invalidator, $product) {
            DB::transaction(function () use ($invalidator, $product) {
                $invalidator->record($product, CacheInvalidationReason::Updated);

                throw new RuntimeException('roll back');
            });
        }, report: false);
    }

    expect($countListeners())->toBe($baseline);
});

test('cache tags use the morph alias and key', function () {
    $product = Product::factory()->create();
    $option = ProductOption::factory()->create();

    expect($product->cacheTags())->toBe(["product:{$product->id}"]);
    expect($option->cacheTags())->toBe(["product_option:{$option->id}"]);
});

test('a deleted product invalidation survives real queue serialization', function () {
    $product = Product::factory()->create();
    $id = $product->id;

    $event = new ProductInvalidated($product, CacheInvalidationReason::Deleted);

    $product->delete();

    $restored = unserialize(serialize($event));

    expect($restored->morphType())->toBe($product->getMorphClass());
    expect($restored->cacheKey())->toBe($id);
    expect($restored->cacheTags())->toBe(["product:{$id}"]);
    expect($restored->reason())->toBe(CacheInvalidationReason::Deleted);
    expect($restored->cacheModel())->toBeInstanceOf(Product::class);
    expect($restored->cacheModel()->id)->toBe($id);
});

test('a deleted collection invalidation survives real queue serialization', function () {
    $collection = Collection::factory()->create();
    $id = $collection->id;

    $event = new CollectionInvalidated($collection, CacheInvalidationReason::Deleted);

    $collection->delete();

    $restored = unserialize(serialize($event));

    expect($restored->cacheKey())->toBe($id);
    expect($restored->cacheTags())->toBe(["collection:{$id}"]);
});

test('a deleted brand invalidation survives real queue serialization', function () {
    $brand = Brand::factory()->create();
    $id = $brand->id;

    $event = new BrandInvalidated($brand, CacheInvalidationReason::Deleted);

    $brand->delete();

    $restored = unserialize(serialize($event));

    expect($restored->cacheKey())->toBe($id);
    expect($restored->cacheTags())->toBe(["brand:{$id}"]);
});

test('a deleted product option invalidation survives real queue serialization', function () {
    $option = ProductOption::factory()->create();
    $id = $option->id;

    $event = new ProductOptionInvalidated($option, CacheInvalidationReason::Deleted);

    $option->delete();

    $restored = unserialize(serialize($event));

    expect($restored->cacheKey())->toBe($id);
    expect($restored->cacheTags())->toBe(["product_option:{$id}"]);
});
