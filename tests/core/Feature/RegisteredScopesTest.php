<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Models\Builders\Builder;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Product;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Schema::table((new Product)->getTable(), function (Blueprint $table) {
        $table->boolean('is_featured')->default(false);
        $table->unsignedInteger('priority')->default(0);
    });
});

afterEach(function () {
    Builder::flushScopes();
    Product::clearBootedModels();
});

test('a registered scope is callable chained on its model', function () {
    Product::addLocalScope('featured', fn (Builder $query) => $query->where('is_featured', true));

    $featured = Product::factory()->create(['is_featured' => true]);
    Product::factory()->create(['is_featured' => false]);

    expect(Product::query()->featured()->pluck('id')->all())->toBe([$featured->id]);
});

test('a registered scope is callable as a static entry point', function () {
    Product::addLocalScope('featured', fn (Builder $query) => $query->where('is_featured', true));

    $featured = Product::factory()->create(['is_featured' => true]);
    Product::factory()->create(['is_featured' => false]);

    expect(Product::featured()->pluck('id')->all())->toBe([$featured->id]);
});

test('a registered scope accepts arguments', function () {
    Product::addLocalScope('priorityOver', fn (Builder $query, int $min) => $query->where('priority', '>', $min));

    Product::factory()->create(['priority' => 3]);
    $high = Product::factory()->create(['priority' => 9]);

    expect(Product::query()->priorityOver(5)->pluck('id')->all())->toBe([$high->id]);
});

test('a registered scope is isolated to its own model', function () {
    Product::addLocalScope('featured', fn (Builder $query) => $query->where('is_featured', true));

    expect(fn () => Order::query()->featured())->toThrow(BadMethodCallException::class);
});

test('a registered scope composes with native scopes', function () {
    Product::addLocalScope('featured', fn (Builder $query) => $query->where('is_featured', true));

    $match = Product::factory()->create(['status' => 'published', 'is_featured' => true]);
    Product::factory()->create(['status' => 'draft', 'is_featured' => true]);
    Product::factory()->create(['status' => 'published', 'is_featured' => false]);

    expect(Product::query()->featured()->whereVisible()->pluck('id')->all())->toBe([$match->id]);
});

test('a native scope wins over a registered scope of the same name', function () {
    // Registering a name that shadows Product::scopeWhereVisible must not
    // override the real scope.
    Product::addLocalScope('whereVisible', fn (Builder $query) => $query->whereRaw('1 = 0'));

    $published = Product::factory()->create(['status' => 'published']);
    Product::factory()->create(['status' => 'draft']);

    expect(Product::query()->whereVisible()->pluck('id')->all())->toBe([$published->id]);
});

test('a registered scope works on a model with a custom builder', function () {
    Schema::table((new Collection)->getTable(), function (Blueprint $table) {
        $table->boolean('is_featured')->default(false);
    });

    Collection::addLocalScope('featured', fn ($query) => $query->where('is_featured', true));

    $featured = Collection::factory()->create(['is_featured' => true]);
    Collection::factory()->create(['is_featured' => false]);

    expect(Collection::query()->featured()->pluck('id')->all())->toBe([$featured->id]);
});
