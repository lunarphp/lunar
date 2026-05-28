<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Lunar\Core\Contracts\FieldTypeManifest;
use Lunar\Core\Enums\FieldTypeEnum;
use Lunar\Core\Exceptions\FieldTypes\FieldTypeMissingException;
use Lunar\Core\Exceptions\FieldTypes\InvalidFieldTypeException;
use Lunar\Core\FieldTypes\Text;
use Lunar\Core\Manifests\FieldTypeManifest as Manifest;
use Lunar\Core\Models\Channel;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('can instantiate class', function () {
    $manifest = app(FieldTypeManifest::class);

    expect($manifest)->toBeInstanceOf(Manifest::class);
});

test('can return types', function () {
    $manifest = app(FieldTypeManifest::class);

    expect($manifest->getTypes())->toBeInstanceOf(Collection::class);
    expect($manifest->getTypes())->not->toBeEmpty();
});

test('resolves the class for a registered type', function () {
    $manifest = app(FieldTypeManifest::class);

    expect($manifest->getType(FieldTypeEnum::Text->value))->toBe(Text::class);
    expect($manifest->getType('does-not-exist'))->toBeNull();
});

test('can add a custom field type', function () {
    $manifest = app(FieldTypeManifest::class);

    $manifest->add('custom_text', Text::class);

    expect($manifest->getType('custom_text'))->toBe(Text::class);
});

test('can remove a field type', function () {
    $manifest = app(FieldTypeManifest::class);

    $manifest->remove(FieldTypeEnum::Text->value);

    expect($manifest->getType(FieldTypeEnum::Text->value))->toBeNull();
});

test('cannot add a non fieldtype class', function () {
    $manifest = app(FieldTypeManifest::class);

    $manifest->add('invalid', Channel::class);
})->throws(InvalidFieldTypeException::class);

test('cannot add a missing class', function () {
    $manifest = app(FieldTypeManifest::class);

    $manifest->add('missing', 'This\\Class\\Does\\Not\\Exist');
})->throws(FieldTypeMissingException::class);
