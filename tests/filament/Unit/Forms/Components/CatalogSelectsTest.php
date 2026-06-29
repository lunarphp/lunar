<?php

use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Tag;
use Lunar\Filament\Forms\Components\BrandSelect;
use Lunar\Filament\Forms\Components\CollectionSelect;
use Lunar\Filament\Forms\Components\ProductTypeSelect;
use Lunar\Filament\Forms\Components\ProductVariantSelect;
use Lunar\Filament\Forms\Components\TagSelect;
use Lunar\Tests\Filament\TestCase;

uses(TestCase::class);

it('instantiates ProductVariantSelect with sensible defaults', function () {
    $component = ProductVariantSelect::make('variant_id')
        ->searchViaProduct()
        ->forProduct(null);

    expect($component)->toBeInstanceOf(ProductVariantSelect::class)
        ->and($component->lunarModel())->toBe(ProductVariant::class)
        ->and($component->isSearchable())->toBeTrue();
});

it('instantiates CollectionSelect with fluent options', function () {
    $component = CollectionSelect::make('collection_id')
        ->excludeAttached();

    expect($component)->toBeInstanceOf(CollectionSelect::class)
        ->and($component->lunarModel())->toBe(Collection::class)
        ->and($component->getLabel())->toBe(__('lunar-filament::forms/selectors.collection.label'));
});

it('instantiates BrandSelect bound to the brand relationship', function () {
    $component = BrandSelect::make('brand_id');

    expect($component)->toBeInstanceOf(BrandSelect::class)
        ->and($component->lunarModel())->toBe(Brand::class)
        ->and($component->getRelationshipName())->toBe('brand')
        ->and($component->isPreloaded())->toBeTrue();
});

it('instantiates ProductTypeSelect preloaded against the productType relationship', function () {
    $component = ProductTypeSelect::make('product_type_id');

    expect($component)->toBeInstanceOf(ProductTypeSelect::class)
        ->and($component->getRelationshipName())->toBe('productType')
        ->and($component->isPreloaded())->toBeTrue();
});

it('instantiates TagSelect with multiple-by-default', function () {
    $component = TagSelect::make('tags');

    expect($component)->toBeInstanceOf(TagSelect::class)
        ->and($component->isMultiple())->toBeTrue()
        ->and($component->lunarModel())->toBe(Tag::class);
});
