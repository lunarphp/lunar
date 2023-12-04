<?php

uses(\Lunar\Tests\TestCase::class);
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;
use Lunar\Models\ProductType;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can make a product type', function () {
    $productType = ProductType::factory()
        ->has(
            Attribute::factory()->for(AttributeGroup::factory())->count(1),
            'mappedAttributes',
        )
        ->create([
            'name' => 'Bob',
        ]);

    expect($productType->name)->toEqual('Bob');
});
