<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Products\UpdateProduct;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
});

test('updates product fields', function () {
    $product = Product::factory()->create();

    app(UpdateProduct::class)->execute($product, [
        'name' => ['en' => 'Renamed'],
        'status' => 'published',
        'brand_id' => null,
    ]);

    $product->refresh();

    expect($product->translate('name'))->toBe('Renamed')
        ->and((string) $product->status)->toBe('published')
        ->and($product->brand_id)->toBeNull();
});

test('syncs tags when given and leaves them untouched when null', function () {
    $product = Product::factory()->create();

    app(UpdateProduct::class)->execute($product, [], tags: ['festive', 'sale']);

    expect($product->refresh()->tags->pluck('value')->sort()->values()->all())
        ->toBe(['FESTIVE', 'SALE']);

    app(UpdateProduct::class)->execute($product, ['name' => ['en' => 'Still tagged']]);

    expect($product->refresh()->tags)->toHaveCount(2);

    app(UpdateProduct::class)->execute($product, [], tags: []);

    expect($product->refresh()->tags)->toHaveCount(0);
});

test('syncs collection membership when given', function () {
    $product = Product::factory()->create();
    $group = CollectionGroup::factory()->create();
    $collections = Collection::factory()->count(2)->create(['collection_group_id' => $group->id]);

    app(UpdateProduct::class)->execute($product, [], collectionIds: $collections->pluck('id')->all());

    expect($product->refresh()->collections)->toHaveCount(2);

    app(UpdateProduct::class)->execute($product, [], collectionIds: [$collections->first()->id]);

    expect($product->refresh()->collections)->toHaveCount(1);
});

test('syncs channel availability pivot rows when given', function () {
    $product = Product::factory()->create();
    $channel = Channel::factory()->create();

    app(UpdateProduct::class)->execute($product, [], channels: [
        $channel->id => ['enabled' => true, 'starts_at' => '2026-08-01 00:00:00', 'ends_at' => null],
    ]);

    $pivot = $product->refresh()->channels->firstWhere('id', $channel->id)->pivot;

    expect((bool) $pivot->enabled)->toBeTrue()
        ->and($pivot->starts_at)->not->toBeNull();
});

test('syncs customer group availability including the purchasable flag', function () {
    $product = Product::factory()->create();
    $group = CustomerGroup::factory()->create();

    app(UpdateProduct::class)->execute($product, [], customerGroups: [
        $group->id => ['enabled' => true, 'visible' => true, 'purchasable' => false],
    ]);

    $pivot = $product->refresh()->customerGroups->firstWhere('id', $group->id)->pivot;

    expect((bool) $pivot->enabled)->toBeTrue()
        ->and((bool) $pivot->visible)->toBeTrue()
        ->and((bool) $pivot->purchasable)->toBeFalse();
});
