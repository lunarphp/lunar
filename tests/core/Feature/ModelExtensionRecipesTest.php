<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Contracts\Actions\Orders\CancelsOrder;
use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductType;
use Lunar\Tests\Core\TestCase;

/**
 * The supported, substitution-free ways to extend a model, exercised against real
 * Lunar models. This is the gate for retiring model class substitution: if a recipe
 * cannot pass here, removal does not proceed.
 */
uses(TestCase::class);
uses(RefreshDatabase::class);

// These recipes describe the post-removal world, where a model has a single class
// identity. Under model extending the canonical class and its replacement are two
// identities, which actively breaks some natives (e.g. an externally-registered
// global scope is dropped because queries run as the replaced class). Asserting
// the recipes against the system being removed is moot, so skip that matrix leg.
beforeEach(function () {
    if (env('LUNAR_TESTING_REPLACE_MODELS')) {
        $this->markTestSkipped('Recipes assert the post-removal single class identity.');
    }
});

// Global scopes live in static state on the model class and outlive a single
// test; clear them so the addGlobalScope recipe cannot leak into other tests.
afterEach(function () {
    Product::clearBootedModels();
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

        public function execute(OrderContract $order, ?string $reason = null, ?string $note = null, bool $notify = true): Order
        {
            $this->called = true;

            return $order;
        }
    };

    app()->instance(CancelsOrder::class, $spy);

    Order::factory()->create()->cancel();

    expect($spy->called)->toBeTrue();
});
