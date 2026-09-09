<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Laravel\Scout\Jobs\MakeSearchable;
use Lunar\Facades\ModelManifest;
use Lunar\Models\Order;
use Lunar\Models\Product;
use Lunar\Models\ProductOption;
use Lunar\Tests\Core\Stubs\Models\Custom\CustomProduct;
use Lunar\Tests\Core\Stubs\Models\Custom\DeepCustomProduct;
use Lunar\Tests\Core\Stubs\Models\CustomOrder;
use Lunar\Tests\Core\Stubs\TestOrderObserver;
use Lunar\Tests\Core\Unit\Base\Extendable\ExtendableTestCase;

uses(ExtendableTestCase::class)->group('model_extending');

uses(RefreshDatabase::class);

beforeEach(
    function () {
        ModelManifest::replace(
            Lunar\Models\Contracts\Product::class,
            Lunar\Tests\Core\Stubs\Models\Product::class
        );

        ModelManifest::replace(
            Lunar\Models\Contracts\ProductOption::class,
            Lunar\Tests\Core\Stubs\Models\ProductOption::class
        );
    }
);

test('can get new instance of the registered model', function () {
    $product = Product::find(1);

    expect($product)->toBeInstanceOf(Lunar\Tests\Core\Stubs\Models\Product::class);
});

test('can forward calls to extended model', function () {
    // @phpstan-ignore-next-line
    $sizeOption = ProductOption::with('sizes')->find(1);

    expect($sizeOption)->toBeInstanceOf(Lunar\Tests\Core\Stubs\Models\ProductOption::class);

    expect($sizeOption->sizes)->toBeInstanceOf(Collection::class);
    expect($sizeOption->sizes)->toHaveCount(1);
});

test('extended model returns correct table name', function () {
    expect((new CustomOrder)->getTable())
        ->toBe(
            (new Order)->getTable()
        );
});

test('can forward static method calls to extended model', function () {
    /** @see Lunar\Tests\Core\Stubs\Models\ProductOption::getSizesStatic() */
    $newStaticMethod = ProductOption::getSizesStatic();

    expect($newStaticMethod)->toBeInstanceOf(Collection::class);
    expect($newStaticMethod)->toHaveCount(3);
});

test('morph map is correct when models are extended', function () {
    ModelManifest::replace(
        Lunar\Models\Contracts\Product::class,
        CustomProduct::class
    );

    expect((new CustomProduct)->getMorphClass())
        ->toBe('product')
        ->and(CustomProduct::morphName())
        ->toBe('product')
        ->and((new Product)->getMorphClass())
        ->toBe('product')
        ->and(Product::morphName())
        ->toBe('product');
});

test('core model events are triggered with extended models', function () {
    Event::fake();

    $product = Lunar\Tests\Core\Stubs\Models\Product::factory()->create();

    $product->delete();

    Event::assertDispatched(
        'eloquent.deleted: '.Product::class
    );

    ModelManifest::replace(
        Lunar\Models\Contracts\Product::class,
        CustomProduct::class
    );

    $product = CustomProduct::factory()->create();

    $product->delete();

    Event::assertDispatched(
        'eloquent.deleted: '.Product::class
    );
});

test('multi-level extended model returns correct table name without prefix duplication', function () {
    $lunarProduct = new Product;
    $customProduct = new CustomProduct;
    $deepCustomProduct = new DeepCustomProduct;

    // All three models should return the same table name
    expect($customProduct->getTable())
        ->toBe($lunarProduct->getTable())
        ->and($deepCustomProduct->getTable())
        ->toBe($lunarProduct->getTable());

    // Verify the table name is correctly prefixed (not duplicated)
    $expectedTable = config('lunar.database.table_prefix').'products';
    expect($deepCustomProduct->getTable())->toBe($expectedTable);

    // Ensure prefix is not duplicated
    expect($deepCustomProduct->getTable())->not->toContain(config('lunar.database.table_prefix').config('lunar.database.table_prefix'));
});

test('observers registered by a trait boot only run once for a replaced model', function () {
    ModelManifest::replace(
        Lunar\Models\Contracts\Order::class,
        Lunar\Tests\Core\Stubs\Models\Order::class
    );

    // Both the Lunar model and its replacement boot their traits, and Scout registers its
    // observer from `bootSearchable()`. Model events are dispatched under both class names, so
    // the observer has to be registered for one of them only.
    config(['scout.queue' => true]);

    Queue::fake();

    Lunar\Tests\Core\Stubs\Models\Order::factory()->create();

    Queue::assertPushed(MakeSearchable::class, 1);
});

test('an observer registered on both the lunar model and its replacement runs once', function () {
    ModelManifest::replace(
        Lunar\Models\Contracts\Order::class,
        Lunar\Tests\Core\Stubs\Models\Order::class
    );

    TestOrderObserver::$created = 0;

    Order::observe(TestOrderObserver::class);
    Lunar\Tests\Core\Stubs\Models\Order::observe(TestOrderObserver::class);

    Lunar\Tests\Core\Stubs\Models\Order::factory()->create();

    expect(TestOrderObserver::$created)->toBe(1);
});
