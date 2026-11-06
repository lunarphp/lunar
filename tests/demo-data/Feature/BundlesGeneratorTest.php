<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Lunar\Bundles\Enums\BundlePricing;
use Lunar\Bundles\Models\Bundle;
use Lunar\Core\Models\ProductVariant;
use Lunar\DemoData\Generators\BundlesGenerator;
use Lunar\DemoData\Generators\CatalogueGenerator;
use Lunar\DemoData\Generators\FoundationGenerator;
use Lunar\DemoData\Support\DemoContext;
use Lunar\Tests\DemoData\BundlesTestCase;

uses(BundlesTestCase::class, RefreshDatabase::class);

function generateBundles(int $products = 4): DemoContext
{
    Storage::fake((string) config('lunar.demo-data.asset_disk', 'public'));
    Config::set('lunar.demo-data.scales.small.products', $products);

    $context = DemoContext::fromConfig('small');

    app(FoundationGenerator::class)->generate($context);
    app(CatalogueGenerator::class)->generate($context);
    app(BundlesGenerator::class)->generate($context);

    return $context;
}

test('it builds a components-priced kit and a configurable gift set', function () {
    generateBundles();

    $kit = ProductVariant::query()->where('sku', 'BND-KIT-001')->first();
    $set = ProductVariant::query()->where('sku', 'BND-SET-002')->first();

    expect($kit->bundle)->toBeInstanceOf(Bundle::class)
        ->and($kit->bundle->pricing)->toBe(BundlePricing::Components)
        ->and($kit->bundle->components)->toHaveCount(3)
        ->and($kit->prices)->not->toBeEmpty()
        ->and($set->bundle->pricing)->toBe(BundlePricing::Fixed)
        ->and($set->bundle->groups)->toHaveCount(1)
        ->and($set->bundle->fixedComponents)->toHaveCount(1)
        ->and($set->bundle->groups->first()->components)->toHaveCount(2);
});

test('a re-run does not duplicate the bundles', function () {
    $context = generateBundles();

    app(BundlesGenerator::class)->generate($context);

    expect(Bundle::query()->count())->toBe(2);
});

test('it is a no-op with too few products', function () {
    generateBundles(products: 2);

    expect(Bundle::query()->count())->toBe(0);
});
