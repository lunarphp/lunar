<?php

uses(\Lunar\Tests\TestCase::class);
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can make a attribute group', function () {
    $attributeGroup = AttributeGroup::factory()->create([
        'attributable_type' => 'product_type',
        'name' => [
            'en' => 'SEO',
        ],
        'handle' => 'seo',
        'position' => 5,
    ]);

    expect($attributeGroup->name->get('en'))->toEqual('SEO');
    expect($attributeGroup->handle)->toEqual('seo');
    expect($attributeGroup->position)->toEqual(5);
});

test('can get associated attributes', function () {
    $attributeGroup = AttributeGroup::factory()->create([
        'attributable_type' => 'product_type',
        'name' => [
            'en' => 'SEO',
        ],
        'handle' => 'seo',
        'position' => 5,
    ]);

    expect($attributeGroup->attributes)->toHaveCount(0);

    $attributeGroup->attributes()->create(
        Attribute::factory()->make()->toArray()
    );

    expect($attributeGroup->refresh()->attributes)->toHaveCount(1);
});
