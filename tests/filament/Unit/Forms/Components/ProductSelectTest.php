<?php

use Lunar\Core\Models\Product;
use Lunar\Filament\Forms\Components\ProductSelect;
use Lunar\Tests\Filament\TestCase;

uses(TestCase::class);

it('can be instantiated without the admin panel booted', function () {
    $component = ProductSelect::make('product_id');

    expect($component)->toBeInstanceOf(ProductSelect::class)
        ->and($component->getName())->toBe('product_id')
        ->and($component->isSearchable())->toBeTrue();
});

it('resolves the configured Product model class', function () {
    $component = ProductSelect::make('product_id');

    expect($component->lunarModel())->toBe(Product::class);
});

it('exposes fluent options without breaking the chain', function () {
    $component = ProductSelect::make('product_id')
        ->showSku()
        ->showThumbnail()
        ->scopeStatus('published')
        ->excludeAttached();

    expect($component)->toBeInstanceOf(ProductSelect::class);
});

it('uses the translated selector label as its default', function () {
    expect(ProductSelect::make('product_id')->getLabel())
        ->toBe(__('lunar-filament::forms/selectors.product.label'));
});
