<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Lunar\Core\Contracts\FieldTypeManifest;
use Lunar\Core\Exceptions\FieldTypes\FieldTypeMissingException;
use Lunar\Core\Exceptions\FieldTypes\InvalidFieldTypeException;
use Lunar\Core\FieldTypes\Manifest;
use Lunar\Core\Models\Cart;
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
});

test('has base types set', function () {
    $manifest = app(FieldTypeManifest::class);

    expect($manifest->getTypes())->toBeInstanceOf(Collection::class);

    expect($manifest->getTypes())->not->toBeEmpty();
});

test('cannot add non fieldtype', function () {
    $manifest = app(FieldTypeManifest::class);

    $this->expectException(
        InvalidFieldTypeException::class
    );

    $manifest->add(Channel::class);

    $this->expectException(
        FieldTypeMissingException::class
    );

    $manifest->add(Cart::class);
});
