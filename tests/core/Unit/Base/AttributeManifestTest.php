<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Illuminate\Support\Collection;
use Lunar\Base\AttributeManifest;
use Lunar\Base\AttributeManifestInterface;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Attribute;
use Lunar\Models\Channel;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can instantiate class', function () {
    $manifest = app(AttributeManifestInterface::class);

    expect($manifest)->toBeInstanceOf(AttributeManifest::class);
});

test('can return types', function () {
    $manifest = app(AttributeManifestInterface::class);

    expect($manifest->getTypes())->toBeInstanceOf(Collection::class);
});

test('has base types set', function () {
    $manifest = app(AttributeManifestInterface::class);

    expect($manifest->getTypes())->toBeInstanceOf(Collection::class);

    expect($manifest->getTypes())->not->toBeEmpty();
});

test('can add type', function () {
    $manifest = app(AttributeManifestInterface::class);

    $manifest->addType(Channel::class);

    expect($manifest->getType('channel'))->not->toBeNull();
});

test('can get searchable attributes', function () {
    $attributeA = Attribute::factory()->create([
        'attribute_type' => 'product',
        'searchable' => true,
    ]);
    $attributeB = Attribute::factory()->create([
        'attribute_type' => 'product',
        'searchable' => true,
    ]);
    Attribute::factory()->create([
        'attribute_type' => 'collection',
        'searchable' => false,
    ]);
    $attributeD = Attribute::factory()->create([
        'attribute_type' => 'collection',
        'type' => TranslatedText::class,
        'searchable' => true,
    ]);

    $manifest = app(AttributeManifestInterface::class);

    $productAttributes = $manifest->getSearchableAttributes('product');
    $collectionAttributes = $manifest->getSearchableAttributes('collection');

    expect($productAttributes)->toHaveCount(2);
    expect($productAttributes->pluck('handle')->toArray())->toBe([$attributeA->handle, $attributeB->handle]);
    expect($collectionAttributes)->toHaveCount(1);
    expect($collectionAttributes->pluck('handle')->toArray())->toBe([$attributeD->handle]);
});
