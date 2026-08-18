<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Lunar\Core\Contracts\AttributeManifest;
use Lunar\Core\Enums\FieldTypeEnum;
use Lunar\Core\Manifests\AttributeManifest as AttributeManifestImpl;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\ProductType;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

function createAttributeForType(string $modelType, bool $searchable, ?string $type = null): Attribute
{
    $attribute = Attribute::factory()->create(array_filter([
        'searchable' => $searchable,
        'type' => $type,
    ], fn ($value) => $value !== null));

    $attribute->models()->create(['model_type' => $modelType]);

    return $attribute;
}

test('can instantiate class', function () {
    $manifest = app(AttributeManifest::class);

    expect($manifest)->toBeInstanceOf(AttributeManifestImpl::class);
});

test('can return types', function () {
    $manifest = app(AttributeManifest::class);

    expect($manifest->getTypes())->toBeInstanceOf(Collection::class);
});

test('has base types set', function () {
    $manifest = app(AttributeManifest::class);

    expect($manifest->getTypes())->toBeInstanceOf(Collection::class);

    expect($manifest->getTypes())->not->toBeEmpty();
});

test('registers product types as an attributable type', function () {
    $manifest = app(AttributeManifest::class);

    expect($manifest->getType('producttype'))->toBe(ProductType::class);
});

test('can add type', function () {
    $manifest = app(AttributeManifest::class);

    $manifest->addType(Channel::class);

    expect($manifest->getType('channel'))->not->toBeNull();
});

test('can get searchable attributes', function () {
    $attributeA = createAttributeForType('product', searchable: true);
    $attributeB = createAttributeForType('product', searchable: true);
    createAttributeForType('collection', searchable: false);
    $attributeD = createAttributeForType('collection', searchable: true, type: FieldTypeEnum::TranslatedText->value);

    $manifest = app(AttributeManifest::class);

    $productAttributes = $manifest->getSearchableAttributes('product');
    $collectionAttributes = $manifest->getSearchableAttributes('collection');

    expect($productAttributes)->toHaveCount(2);
    expect($productAttributes->pluck('handle')->toArray())->toBe([$attributeA->handle, $attributeB->handle]);
    expect($collectionAttributes)->toHaveCount(1);
    expect($collectionAttributes->pluck('handle')->toArray())->toBe([$attributeD->handle]);
});
