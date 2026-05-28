<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\AttributeGroup;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class)->group('models');

use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

test('can make a attribute group', function () {
    $attributeGroup = AttributeGroup::factory()->create([
        'name' => 'SEO',
        'handle' => 'seo',
        'position' => 5,
    ]);

    expect($attributeGroup->name)->toEqual('SEO');
    expect($attributeGroup->handle)->toEqual('seo');
    expect($attributeGroup->position)->toEqual(5);
});

test('handle is slugified', function () {
    $attributeGroup = AttributeGroup::factory()->create([
        'handle' => 'Product SEO',
    ]);

    expect($attributeGroup->handle)->toEqual('product_seo');
});

test('can get associated attributes', function () {
    $attributeGroup = AttributeGroup::factory()->create();

    expect($attributeGroup->attributes()->count())->toBe(0);

    $attributeGroup->attributes()->create(
        Attribute::factory()->make(['attribute_group_id' => null])->toArray()
    );

    expect($attributeGroup->refresh()->attributes()->count())->toBe(1);
});

test('orders associated attributes by position', function () {
    $attributeGroup = AttributeGroup::factory()->create();

    $second = Attribute::factory()->create(['attribute_group_id' => $attributeGroup->id, 'position' => 2]);
    $first = Attribute::factory()->create(['attribute_group_id' => $attributeGroup->id, 'position' => 1]);

    expect($attributeGroup->attributes->pluck('id')->toArray())->toBe([$first->id, $second->id]);
});

test('can delete attribute group', function () {
    $attributeGroup = AttributeGroup::factory()->create();

    $attributeGroup->delete();

    assertDatabaseMissing(AttributeGroup::class, [
        'id' => $attributeGroup->id,
    ]);
});
