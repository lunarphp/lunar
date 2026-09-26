<?php

use Lunar\Api\OpenApi\Schema;
use Lunar\Api\OpenApi\TypeInference;
use Lunar\Api\Resources\Embed;
use Lunar\Api\Resources\Field;
use Lunar\Api\Resources\Filter;
use Lunar\Api\Resources\Translations;
use Lunar\Api\Storefront\Resources\V1\BrandResource;
use Lunar\Api\Storefront\Resources\V1\ProductVariantResource;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Url;
use Lunar\Tests\Api\TestCase;

uses(TestCase::class);

test('attribute types come from casts and dates without touching the database', function (): void {
    expect(TypeInference::attribute(ProductVariant::class, 'enabled')->toArray())->toBe(['type' => 'boolean']);
    expect(TypeInference::attribute(ProductVariant::class, 'stock_on_hand')->toArray())->toBe(['type' => 'integer']);
    expect(TypeInference::attribute(ProductVariant::class, 'selling_policy')->toArray()['enum'])->toContain('always', 'in_stock');
    expect(TypeInference::attribute(ProductVariant::class, 'sku')->toArray())->toBe(['type' => 'string']);
    expect(TypeInference::attribute(ProductVariant::class, 'created_at')->toArray())->toBe(['type' => 'string', 'format' => 'date-time']);
    expect(TypeInference::attribute(Cart::class, 'completed_at')->toArray())->toBe(['type' => 'string', 'format' => 'date-time']);
    expect(TypeInference::attribute(Cart::class, 'meta')->toArray())->toBe([]);
    expect(TypeInference::attribute(Product::class, 'name')->toArray())->toBe([]);
    expect(TypeInference::attribute(Product::class, 'status')->toArray())->toBe(['type' => 'string']);
    expect(TypeInference::attribute(Url::class, 'default')->toArray())->toBe(['type' => 'boolean']);
});

test('fields infer from the attribute they read, honour declarations and nullability', function (): void {
    expect(TypeInference::field(Field::make('enabled'), ProductVariant::class, Translations::Map)->toArray())->toBe(['type' => 'boolean']);
    expect(TypeInference::field(Field::make('gtin')->nullable(), ProductVariant::class, Translations::Map)->toArray())->toBe(['type' => ['string', 'null']]);
    expect(TypeInference::field(Field::make('stock', fn () => 1)->type(Schema::integer()), ProductVariant::class, Translations::Map)->toArray())->toBe(['type' => 'integer']);
    expect(TypeInference::field(Field::make('stock', fn () => 1), ProductVariant::class, Translations::Map)->isUntyped())->toBeTrue();
});

test('translatable fields are strings when resolved and locale maps otherwise', function (): void {
    $field = Field::translatable('name');

    expect(TypeInference::field($field, Product::class, Translations::Resolved)->toArray())->toBe(['type' => 'string']);
    expect(TypeInference::field($field, Product::class, Translations::Map)->toArray())->toBe(['$ref' => '#/components/schemas/TranslationMap']);
    expect(TypeInference::field(Field::translatable('description')->nullable(), Product::class, Translations::Resolved)->toArray())->toBe(['type' => ['string', 'null']]);
});

test('filters infer from their column, default scopes to boolean and need a declaration otherwise', function (): void {
    expect(TypeInference::filter(Filter::exact('id', 'public_id'), Product::class)->toArray())->toBe(['type' => 'string']);
    expect(TypeInference::filter(Filter::column('updated_at'), Product::class)->toArray())->toBe(['type' => 'string', 'format' => 'date-time']);
    expect(TypeInference::filter(Filter::scope('featured'), Product::class)->toArray())->toBe(['type' => 'boolean']);
    expect(TypeInference::filter(Filter::make('brand', fn () => null), Product::class)->isUntyped())->toBeTrue();
    expect(TypeInference::filter(Filter::make('brand', fn () => null)->type(Schema::string()), Product::class)->toArray())->toBe(['type' => 'string']);
});

test('include cardinality comes from the relation type or an explicit declaration', function (): void {
    expect(TypeInference::many(Embed::relation('variants', ProductVariantResource::class), Product::class))->toBeTrue();
    expect(TypeInference::many(Embed::relation('brand', BrandResource::class), Product::class))->toBeFalse();
    expect(TypeInference::many(Embed::relation('collections', BrandResource::class), Product::class))->toBeTrue();
    expect(TypeInference::many(Embed::make('top', fn () => null, BrandResource::class), Product::class))->toBeFalse();
    expect(TypeInference::many(Embed::make('top', fn () => null, BrandResource::class)->many(), Product::class))->toBeTrue();
    expect(TypeInference::many(Embed::relation('missing', BrandResource::class), Product::class))->toBeFalse();
});
