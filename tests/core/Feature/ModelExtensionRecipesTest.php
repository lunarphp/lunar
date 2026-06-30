<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Contracts\Actions\Orders\CancelsOrder;
use Lunar\Core\Models\Builders\Builder as LunarBuilder;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductType;
use Lunar\Tests\Core\TestCase;

/**
 * The supported ways to extend a Lunar model without subclassing — relationships,
 * methods, casts, query scopes, lifecycle hooks, and behaviour overrides — each
 * exercised against a real Lunar model.
 */
uses(TestCase::class);
uses(RefreshDatabase::class);

// Global scopes live in static state on the model class and outlive a single
// test; clear them so the addGlobalScope recipe cannot leak into other tests.
afterEach(function () {
    Product::clearBootedModels();
    LunarBuilder::flushScopes();
});

test('recipe: add a relationship with resolveRelationUsing', function () {
    Product::resolveRelationUsing(
        'altType',
        fn (Product $product) => $product->belongsTo(ProductType::class, 'product_type_id'),
    );

    $product = Product::factory()->create();

    expect($product->altType)
        ->toBeInstanceOf(ProductType::class)
        ->and($product->altType->id)->toBe($product->product_type_id);
});

test('recipe: add a method with a model macro', function () {
    // Must be a real closure, not an arrow fn: Macroable rebinds the macro to the
    // model instance via bindTo, which arrow functions ignore.
    Product::macro('hasIdentity', function () {
        return $this->exists;
    });

    $product = Product::factory()->create();

    expect($product->hasIdentity())->toBeTrue();
});

test('recipe: an added column is a fillable attribute', function () {
    Schema::table((new Product)->getTable(), function (Blueprint $table) {
        $table->string('external_ref')->nullable();
    });

    $product = Product::factory()->create(['external_ref' => 'EXT-123']);

    expect($product->fresh()->external_ref)->toBe('EXT-123');
});

test('recipe: constrain every query with an external global scope', function () {
    $keep = Product::factory()->create();
    Product::factory()->create();

    Product::addGlobalScope('onlyKeep', fn (Builder $query) => $query->whereKey($keep->id));

    expect(Product::pluck('id')->all())->toBe([$keep->id]);
});

test('recipe: react to a lifecycle event with an external listener', function () {
    $fired = 0;
    Event::listen('eloquent.saved: '.Product::class, function () use (&$fired) {
        $fired++;
    });

    Product::factory()->create();

    expect($fired)->toBe(1);
});

test('recipe: override behaviour by binding the action contract', function () {
    $spy = new class implements CancelsOrder
    {
        public bool $called = false;

        public function execute(Order $order, ?string $reason = null, ?string $note = null, bool $notify = true): Order
        {
            $this->called = true;

            return $order;
        }
    };

    app()->instance(CancelsOrder::class, $spy);

    Order::factory()->create()->cancel();

    expect($spy->called)->toBeTrue();
});

test('recipe: register an optional local scope with addLocalScope', function () {
    Schema::table((new Product)->getTable(), function (Blueprint $table) {
        $table->boolean('is_featured')->default(false);
    });

    Product::addLocalScope('featured', fn (LunarBuilder $query) => $query->where('is_featured', true));

    $featured = Product::factory()->create(['is_featured' => true]);
    Product::factory()->create(['is_featured' => false]);

    expect(Product::query()->featured()->pluck('id')->all())->toBe([$featured->id]);
});

test('recipe: cast an added column with addCasts', function () {
    Schema::table((new Product)->getTable(), function (Blueprint $table) {
        $table->string('priority')->nullable();
    });

    Product::addCasts(['priority' => 'integer']);

    $product = Product::factory()->create(['priority' => '5']);

    expect($product->fresh()->priority)->toBe(5);
});

test('recipe: register a custom cast class with addCasts', function () {
    Schema::table((new Product)->getTable(), function (Blueprint $table) {
        $table->json('metadata')->nullable();
    });

    Product::addCasts(['metadata' => AsArrayObject::class]);

    $product = Product::factory()->create(['metadata' => ['source' => 'import']]);

    expect($product->fresh()->metadata)
        ->toBeInstanceOf(ArrayObject::class)
        ->and($product->fresh()->metadata['source'])->toBe('import');
});
