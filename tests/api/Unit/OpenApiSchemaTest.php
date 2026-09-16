<?php

use Lunar\Api\OpenApi\Schema;
use Lunar\Api\Storefront\Resources\V1\BrandResource;
use Lunar\Core\Enums\SellingPolicy;

test('scalar schemas emit their JSON Schema type', function (): void {
    expect(Schema::string()->toArray())->toBe(['type' => 'string']);
    expect(Schema::integer()->toArray())->toBe(['type' => 'integer']);
    expect(Schema::number()->toArray())->toBe(['type' => 'number']);
    expect(Schema::boolean()->toArray())->toBe(['type' => 'boolean']);
    expect(Schema::dateTime()->toArray())->toBe(['type' => 'string', 'format' => 'date-time']);
    expect(Schema::date()->toArray())->toBe(['type' => 'string', 'format' => 'date']);
    expect(Schema::any()->toArray())->toBe([]);
});

test('enums come from an enum class or literal values', function (): void {
    expect(Schema::enum(SellingPolicy::class)->toArray())->toBe([
        'type' => 'string',
        'enum' => ['always', 'in_stock', 'in_stock_or_on_backorder'],
    ]);
    expect(Schema::enum(['draft', 'published'])->toArray())->toBe(['type' => 'string', 'enum' => ['draft', 'published']]);
    expect(Schema::enum([1, 2, 3])->toArray())->toBe(['type' => 'integer', 'enum' => [1, 2, 3]]);
});

test('structural schemas nest their children', function (): void {
    expect(Schema::array(Schema::string())->toArray())->toBe(['type' => 'array', 'items' => ['type' => 'string']]);

    expect(Schema::object(['handle' => Schema::string(), 'count' => Schema::integer()], required: ['handle'])->toArray())->toBe([
        'type' => 'object',
        'properties' => ['handle' => ['type' => 'string'], 'count' => ['type' => 'integer']],
        'required' => ['handle'],
    ]);

    expect(Schema::map(Schema::any())->toArray())->toBe(['type' => 'object', 'additionalProperties' => []]);
});

test('component references resolve through the naming closure', function (): void {
    expect(Schema::money()->toArray())->toBe(['$ref' => '#/components/schemas/Money']);
    expect(Schema::translations()->toArray())->toBe(['$ref' => '#/components/schemas/TranslationMap']);

    expect(Schema::ref(BrandResource::class)->toArray())->toBe(['$ref' => '#/components/schemas/Brand']);
    expect(Schema::ref(BrandResource::class)->toArray(fn (string $class) => 'StorefrontBrand'))->toBe(['$ref' => '#/components/schemas/StorefrontBrand']);
    expect(Schema::array(Schema::ref(BrandResource::class))->toArray(fn () => 'B'))->toBe(['type' => 'array', 'items' => ['$ref' => '#/components/schemas/B']]);
    expect(Schema::ref(BrandResource::class)->resource())->toBe(BrandResource::class);
});

test('nullable adds null to the type or wraps a reference in oneOf', function (): void {
    expect(Schema::string()->nullable()->toArray())->toBe(['type' => ['string', 'null']]);
    expect(Schema::enum(['a', 'b'])->nullable()->toArray())->toBe(['type' => ['string', 'null'], 'enum' => ['a', 'b', null]]);
    expect(Schema::money()->nullable()->describe('Price')->toArray())->toBe([
        'description' => 'Price',
        'oneOf' => [['$ref' => '#/components/schemas/Money'], ['type' => 'null']],
    ]);
    expect(Schema::any()->nullable()->toArray())->toBe([]);
    expect(Schema::string()->nullable()->isNullable())->toBeTrue();
    expect(Schema::string()->isNullable())->toBeFalse();
});

test('annotations and vendor extensions are carried and the value is immutable', function (): void {
    $base = Schema::string();
    $annotated = $base->describe('A name')->example('Widget')->format('slug')->extension('x-lunar-requires', ['catalog:read']);

    expect($base->toArray())->toBe(['type' => 'string']);
    expect($annotated->toArray())->toBe([
        'type' => 'string',
        'description' => 'A name',
        'example' => 'Widget',
        'format' => 'slug',
        'x-lunar-requires' => ['catalog:read'],
    ]);

    expect(fn () => Schema::string()->extension('x-mint', []))->toThrow(InvalidArgumentException::class);
});

test('allOf composes schemas and untyped is flagged', function (): void {
    expect(Schema::allOf(Schema::ref(BrandResource::class), Schema::object(['token' => Schema::string()]))->toArray())->toBe([
        'allOf' => [
            ['$ref' => '#/components/schemas/Brand'],
            ['type' => 'object', 'properties' => ['token' => ['type' => 'string']]],
        ],
    ]);

    expect(Schema::untyped()->isUntyped())->toBeTrue();
    expect(Schema::untyped()->toArray())->toBe(['x-lunar-untyped' => true]);
    expect(Schema::string()->isUntyped())->toBeFalse();
});

test('schemas JSON-encode as their array form', function (): void {
    expect(json_encode(Schema::array(Schema::integer())))->toBe('{"type":"array","items":{"type":"integer"}}');
});
