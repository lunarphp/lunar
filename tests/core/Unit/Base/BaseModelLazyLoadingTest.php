<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lunar\Core\Base\LunarLazyLoading as LunarLazyLoadingManager;
use Lunar\Core\Exceptions\LunarLazyLoadingViolation;
use Lunar\Core\Facades\LunarLazyLoading;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    app()->forgetInstance(LunarLazyLoadingManager::class);
    LunarLazyLoading::clearResolvedInstances();
});

test('lazy loading on a Lunar model throws when enabled', function () {
    Config::set('lunar.database.prevent_lazy_loading', true);

    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id]);

    $fresh = Product::query()->find($product->id);

    expect(fn () => $fresh->variants)->toThrow(LunarLazyLoadingViolation::class);
});

test('lazy loading is a no-op when disabled', function () {
    Config::set('lunar.database.prevent_lazy_loading', false);

    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id]);

    $fresh = Product::query()->find($product->id);

    expect($fresh->variants)->not->toBeNull();
});

test('eager-loaded relations do not throw when enabled', function () {
    Config::set('lunar.database.prevent_lazy_loading', true);

    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id]);

    $fresh = Product::query()->with('variants')->find($product->id);

    expect($fresh->variants)->not->toBeNull();
});

test('custom violation handler is honoured', function () {
    Config::set('lunar.database.prevent_lazy_loading', true);

    $captured = [];

    LunarLazyLoading::handleViolationUsing(function (Model $model, string $relation) use (&$captured) {
        $captured[] = [get_class($model), $relation];
    });

    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id]);

    $fresh = Product::query()->find($product->id);
    $fresh->variants;

    expect($captured)->toHaveCount(1);
    expect($captured[0][0])->toBe(Product::class);
    expect($captured[0][1])->toBe('variants');

    LunarLazyLoading::handleViolationUsing(null);
});

test('auto resolves to enabled outside production', function () {
    Config::set('lunar.database.prevent_lazy_loading', 'auto');

    app()->detectEnvironment(fn () => 'testing');

    expect(LunarLazyLoading::enabled())->toBeTrue();
});

test('auto resolves to disabled in production', function () {
    Config::set('lunar.database.prevent_lazy_loading', 'auto');

    app()->detectEnvironment(fn () => 'production');

    expect(LunarLazyLoading::enabled())->toBeFalse();
});

test('new instances do not throw because they were just created', function () {
    Config::set('lunar.database.prevent_lazy_loading', true);

    $product = Product::factory()->create();

    expect($product->variants)->not->toBeNull();
});
