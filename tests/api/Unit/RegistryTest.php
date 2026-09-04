<?php

use Lunar\Api\Contracts\ApiManager;
use Lunar\Api\Exceptions\ResourceDefinitionException;
use Lunar\Api\Registry\SurfaceRegistry;
use Lunar\Api\Storefront\Resources\V1\BrandResource;
use Lunar\Api\Storefront\Resources\V1\ProductResource;
use Lunar\Core\Models\Product;
use Lunar\Tests\Api\Fixtures\CustomProductResource;
use Lunar\Tests\Api\Fixtures\ReviewsProductExtension;
use Lunar\Tests\Api\TestCase;

uses(TestCase::class);

function registry(): SurfaceRegistry
{
    return new SurfaceRegistry('storefront', 'v9', app());
}

test('resources resolve by class, replacement class and type', function (): void {
    $registry = registry()->resource(ProductResource::class, BrandResource::class);

    expect($registry->types())->toBe(['products', 'brands']);
    expect($registry->definition('products')->model())->toBe(Product::class);
    expect($registry->definition(ProductResource::class))->toBe($registry->definition('products'));
    expect($registry->has('brands'))->toBeTrue();
    expect($registry->has('widgets'))->toBeFalse();
});

test('an unregistered resource is a definition error', function (): void {
    registry()->definition('widgets');
})->throws(ResourceDefinitionException::class);

test('two resources cannot share a wire type', function (): void {
    registry()->resource(ProductResource::class, CustomProductResource::class);
})->throws(ResourceDefinitionException::class, 'type [products]');

test('extensions merge into the definition and survive a replacement', function (): void {
    $registry = registry()
        ->resource(ProductResource::class, BrandResource::class)
        ->extend(ProductResource::class, ReviewsProductExtension::class);

    expect($registry->definition('products')->field('average_rating'))->not->toBeNull();
    expect($registry->definition('products')->filter('featured'))->not->toBeNull();
    expect($registry->definition('products')->sort('rating'))->not->toBeNull();
    expect($registry->definition('products')->extensionRoutes())->toHaveCount(1);

    $registry->replace(ProductResource::class, CustomProductResource::class);

    $definition = $registry->definition(ProductResource::class);

    expect($definition->resource)->toBeInstanceOf(CustomProductResource::class);
    expect($definition->field('custom'))->not->toBeNull();
    expect($definition->field('average_rating'))->not->toBeNull();
    expect($registry->definition(CustomProductResource::class))->toBe($definition);
});

test('a replacement must extend the resource it replaces', function (): void {
    registry()->resource(ProductResource::class)->replace(ProductResource::class, BrandResource::class);
})->throws(ResourceDefinitionException::class, 'must extend');

test('the manager keeps one registry per surface version', function (): void {
    $api = app(ApiManager::class);

    expect($api->storefront('v1'))->toBe($api->surface('storefront', 'v1'));
    expect($api->storefront('v2'))->not->toBe($api->storefront('v1'));
    expect($api->admin('v1')->surface)->toBe('admin');
    expect(array_keys($api->surfaces()))->toContain('storefront:v1', 'admin:v1');
});
